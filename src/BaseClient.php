<?php
/**
 * This file is part of the spaces-api-client package.
 *
 * (c) 2019 Eman Development & Design
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Edd\SpacesApiClient;

use DateTime;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class BaseClient
 * @package Edd\SpacesApiClient
 */
abstract class BaseClient
{
    /**
     * @var \DateTime
     */
    protected $currentDateTime;

    /**
     * @var string
     */
    protected $accessKey;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $space = '';

    /**
     * @var string
     */
    protected $region = 'nyc3';

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $signedHeaders = 'host;x-amz-date';

    /**
     * @var array
     */
    protected $canonicalHeaders = [];

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    protected $http;

    /**
     * BaseClient constructor.
     * @param string $accessKey
     * @param string $secret
     * @param string $host
     * @param string $space
     * @param string $region
     * @throws \Exception
     */
    public function __construct(string $accessKey, string $secret, string $host, string $space = '', string $region = 'nc3')
    {
        $this->currentDateTime = new DateTime('UTC');
        $this->http = HttpClient::create();
        $this->accessKey = $accessKey;
        $this->secret = $secret;
        $this->host = $host;
        $this->space = $space;
        $this->region = $region;

        $iso8601Date = $this->currentDateTime->format('Ymd\THis\Z');
    }

    /**
     * @param string $requestUri
     * @param string $queryString
     * @param string $method
     * @param string $data
     * @return array
     */
    protected function BuildHeaders(string $requestUri, string $queryString = '', string $method = 'GET', string $data = '') : array
    {
        $reqDate = $this->currentDateTime->format('Ymd');
        $reqDateTime = $this->currentDateTime->format('Ymd\THis\Z');

        // Canonical headers
        $this->canonicalHeaders[] = 'host:' . $this->host;
        $this->canonicalHeaders[] = 'x-amz-date:' . $reqDateTime;
        $canonicalHeadersStr = implode("\n", $this->canonicalHeaders);

        // Hashed Payload
        $hashedPayload = hash('sha256', $data);

        // Canonical request
        $canonicalRequest = [];
        $canonicalRequest[] = $method;
        $canonicalRequest[] = $requestUri;
        $canonicalRequest[] = $queryString;
        $canonicalRequest[] = $canonicalHeadersStr . "\n";
        $canonicalRequest[] = $this->signedHeaders;
        $canonicalRequest[] = $hashedPayload;

        $canonicalRequestHashed = hash('sha256', utf8_encode(implode("\n", $canonicalRequest)));

        // String to sign
        $stringToSign = [];
        $stringToSign[] = 'AWS4-HMAC-SHA256';
        $stringToSign[] = $reqDateTime;
        $stringToSign[] = sprintf('%s/%s/s3/aws4_request', $reqDate, $this->region);
        $stringToSign[] = $canonicalRequestHashed;
        $stringToSignStr = implode("\n", $stringToSign);

        // Hash Keys
        $datekey = hash_hmac('sha256', $reqDate, 'AWS4'. $this->secret, true);
        $dateRegionKey = hash_hmac('sha256', $this->region, $datekey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);

        $signature = hash_hmac('sha256', $stringToSignStr, $signingKey);
        $credential = sprintf('%s/%s/%s/s3/aws4_request', $this->accessKey, $reqDate, $this->region);

        return [
            sprintf('authorization: AWS4-HMAC-SHA256 Credential=%s, SignedHeaders=%s, Signature=%s', $credential, $this->signedHeaders, $signature),
            sprintf('host:%s', $this->host),
            sprintf('x-amz-content-sha256:%s', $hashedPayload),
            sprintf('x-amz-date:%s', $reqDateTime)
        ];
    }
}