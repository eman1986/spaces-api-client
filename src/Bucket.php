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
     * @param string $bucketName
     * @param string $acl
     * @return string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function Create(string $bucketName, string $acl) : string
    {
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
}