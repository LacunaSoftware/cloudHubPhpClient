<?php

namespace Lacuna\CloudHub;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class RestClient
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(string $baseUrl, string $apiKey) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
    }

    public function getRestClient()
    {
        $headers = [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ];
        return new Client([
            'base_uri' => $this->baseUrl,
            'headers' => $headers,
            'http_errors' => true,
            'verify' => false
        ]);
    }

    public function get($url)
    {
        $verb = 'GET';
        $client = $this->getRestClient();
        $httpResponse = null;
        try {
            $httpResponse = $client->get($url);
        } catch (TransferException $ex) {
            throw $ex;
        }
        return json_decode($httpResponse->getBody());
    }

    public function post($url, $data)
    {
        $verb = 'POST';
        $client = $this->getRestClient();
        $httpResponse = null;
        try {
            if (empty($data)) {
                $httpResponse = $client->post($url);
            } else {
                $httpResponse = $client->post($url, array('json' => $data));
            }
        } catch (TransferException $ex) {
            throw $ex;
        }
        return json_decode($httpResponse->getBody(), true);
    }
}


