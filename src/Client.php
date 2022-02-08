<?php

namespace Pinata;

use GuzzleHttp\Client as HttpClient;

class Client
{
    private $client;

    function __construct(string $apiKey, string $secretKey)
    {
        $client = new HttpClient(
            [
                'base_uri' => 'https://api.pinata.cloud',
                'headers' => [
                    'pinata_api_key' => $apiKey,
                    'pinata_secret_api_key' => $secretKey,
                ],
            ]
        );

        $this->client = $client;
    }

    public function addHashToPinQueue(string $hashToPin): array
    {
        return $this->doCall('/pinning/addHashToPinQueue', 'POST', ['hashToPin' => $hashToPin]);
    }

    public function pinFileToIPFS(string $filePath, array $metadata = null): array
    {
        return json_decode($this->client->post('/pinning/pinFileToIPFS', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => fopen($filePath, 'r')
                ],
            ]
        ])->getBody()->getContents(), true);
    }

    public function pinHashToIPFS(string $hashToPin): array
    {
        return $this->doCall('/pinning/pinHashToIPFS', 'POST', ['hashToPin' => $hashToPin]);
    }

    public function pinJobs(): array
    {
        return json_decode($this->client->get('/pinning/pinJobs')->getBody()->getContents(), true);
    }

    public function pinJSONToIPFS(array $json, array $metadata = null): array
    {
        $content = ($metadata) ? ['pinataMetadata' => $metadata, 'pinataContent' => $json] : $json;
        return $this->doCall('/pinning/pinJSONToIPFS', 'POST', $content);
    }

    public function removePinFromIPFS(string $hash): bool
    {
        $return = $this->client->post('/pinning/removePinFromIPFS', [
            \GuzzleHttp\RequestOptions::JSON => ['ipfs_pin_hash' => $hash],
        ]);

        return $return->getStatusCode() === 200;
    }

    public function userPinnedDataTotal(): array
    {
        return json_decode($this->client->get('/data/userPinnedDataTotal')->getBody()->getContents(), true);
    }

    public function userPinList(): array
    {
        return json_decode($this->client->get('/data/userPinList')->getBody()->getContents(), true);
    }

    private function doCall(string $endpoint, string $method = 'POST', array $params = []): array
    {
        $response = $this->client->request(
            $method,
            $endpoint,
            [
                \GuzzleHttp\RequestOptions::JSON => $params,
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }
}
