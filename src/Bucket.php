<?php
/**
 * This file is part of the spaces-api-client package.
 *
 * (c) 2020 Ed Lomonaco
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

    /**
     * Get a Bucket's Region
     * @param string $bucket
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function GetBucketRegion(string $bucket) : string
    {
        $response = $this->http->request('GET', sprintf('%s.%s.digitaloceanspaces.com?location', $bucket, $this->region),
            [
                'headers' => $this->BuildHeaders('/', 'location')
            ]);

        return $response->getContent();
    }

    /**
     * Get a Bucket's ACLs
     * @param string $bucket
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function GetBucketAcl(string $bucket) : string
    {
        $response = $this->http->request('GET', sprintf('%s.%s.digitaloceanspaces.com?acl', $bucket, $this->region),
            [
                'headers' => $this->BuildHeaders('/', 'acl')
            ]);

        return $response->getContent();
    }

    public function SetBucketAcl(string $bucket) : string
    {

    }

    /**
     * Delete an empty bucket
     * @param string $bucket
     * @return int
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function Delete(string $bucket) : int
    {
        $response = $this->http->request('DELETE', sprintf('%s.%s.digitaloceanspaces.com', $bucket, $this->region),
            [
                'headers' => $this->BuildHeaders('/')
            ]);

        return $response->getStatusCode();
    }
}