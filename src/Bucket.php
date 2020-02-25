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

class Bucket extends BaseClient
{
    /**
     * Creates a new bucket
     * @param string $bucketName
     * @param string $acl
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function Create(string $bucketName, string $acl = 'private') : string
    {
        if ($acl !== 'private')
        {
            $this->signedHeaders = 'host;x-amz-acl;x-amz-date';
            $this->canonicalHeaders[] = 'x-amz-acl:' . $acl;
        }

        $response = $this->http->request('PUT', sprintf('%s.%s.digitaloceanspaces.com', $bucketName, $this->region),
            [
                'headers' => $this->BuildHeaders('/')
            ]);

        return $response->getContent();
    }

    /**
     * List all existing buckets in a region
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function ListBuckets() : string
    {
        $response = $this->http->request('GET', sprintf('%s.digitaloceanspaces.com', $this->region),
            [
                'headers' => $this->BuildHeaders('/')
            ]);

        return $response->getContent();
    }

    /**
     * Lists the contents of the bucket
     * @param string $delimiter
     * @param string $prefix
     * @param string $marker
     * @param int $maxKeys
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function ListBucketContents(string $delimiter = '', string $prefix = '', string $marker = '', int $maxKeys = 1000) : string
    {
        $queryStrArr = [];

        if ($delimiter !== '')
        {
            $queryStrArr['delimiter'] = $delimiter;
        }

        if ($prefix !== '')
        {
            $queryStrArr['prefix'] = $prefix;
        }

        if ($marker !== '')
        {
            $queryStrArr['marker'] = $marker;
        }

        if ($maxKeys !== 1000)
        {
            $queryStrArr['maxKeys'] = $maxKeys;
        }

        $qs = http_build_query($queryStrArr);

        $response = $this->http->request('GET', sprintf('%s.digitaloceanspaces.com', $this->region),
            [
                'headers' => $this->BuildHeaders('/', $qs)
            ]);

        return $response->getContent();
    }
}