<?php

use GuzzleHttp\Client;

class NotionClient
{
    private Client $client;
    private string $token;
    private string $version;

    public function __construct(string $token, string $version = '2022-06-28')
    {
        $this->token = $token;
        $this->version = $version;
        $this->client = new Client([
            'base_uri' => 'https://api.notion.com/v1/',
            'timeout'  => 10
        ]);
    }

    public function saveToNotion(string $method, string $uri, array $payload): array
    {
        $response = $this->client->request($method, $uri, [
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Notion-Version' => $this->version,
                'Content-Type' => 'application/json'
            ],
            'json' => $payload
        ]);

        return json_decode($response->getBody(), true);
    }
}
