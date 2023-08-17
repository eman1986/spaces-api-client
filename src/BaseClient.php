<?php
/**
 * This file is part of the spaces-api-client package.
 *
 * (c) 2023 Ed Lomonaco
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Edd\SpacesApiClient;

use DateTime;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class BaseClient
{
    protected DateTime $currentDateTime;

    protected string $accessKey;

    protected string $secret;

    protected string $space = '';

    protected string $region = 'nyc3';

    protected string $host;

    protected string $signedHeaders = 'host;x-amz-date';

    protected array $canonicalHeaders = [];

    protected HttpClientInterface $http;

    protected readonly Serializer $serializer;

    public function __construct(string $accessKey, string $secret, string $host, string $space = '', string $region = 'nyc3')
    {
        $this->currentDateTime = new DateTime('UTC');
        $this->http = HttpClient::create();
        $this->accessKey = $accessKey;
        $this->secret = $secret;
        $this->host = $host;
        $this->space = $space;
        $this->region = $region;

        // setup the serializer
        $encoders = [new XmlEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @param string $requestUri
     * @param string $queryString
     * @param string $method
     * @param string $data
     * @return array
     */
    protected function BuildHeaders(string $requestUri, string $queryString = '', string $method = 'GET', string $data = ''): array
    {
        $reqDate = $this->currentDateTime->format('Ymd');
        $iso8601Date = $this->currentDateTime->format('Ymd\THis\Z');

        // Canonical headers
        $this->canonicalHeaders[] = 'host:' . $this->host;
        $this->canonicalHeaders[] = 'x-amz-date:' . $iso8601Date;
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

        $canonicalRequestHashed = hash('sha256', mb_convert_encoding(implode("\n", $canonicalRequest), 'UTF-8'));

        // String to sign
        $stringToSign = [];
        $stringToSign[] = 'AWS4-HMAC-SHA256';
        $stringToSign[] = $iso8601Date;
        $stringToSign[] = sprintf('%s/%s/s3/aws4_request', $reqDate, $this->region);
        $stringToSign[] = $canonicalRequestHashed;
        $stringToSignStr = implode("\n", $stringToSign);

        // Hash Keys
        $dateKey = hash_hmac('sha256', $reqDate, 'AWS4'. $this->secret, true);
        $dateRegionKey = hash_hmac('sha256', $this->region, $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);

        $signature = hash_hmac('sha256', $stringToSignStr, $signingKey);
        $credential = sprintf('%s/%s/%s/s3/aws4_request', $this->accessKey, $reqDate, $this->region);

        return [
            sprintf('authorization: AWS4-HMAC-SHA256 Credential=%s, SignedHeaders=%s, Signature=%s', $credential, $this->signedHeaders, $signature),
            sprintf('host:%s', $this->host),
            sprintf('x-amz-content-sha256:%s', $hashedPayload),
            sprintf('x-amz-date:%s', $iso8601Date)
        ];
    }
}