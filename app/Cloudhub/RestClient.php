<?php

namespace Lacuna\Cloudhub;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class RestClient
{
    private string $apiKey;
    private string $baseUrl;
    private $handler;

    public function __construct(string $baseUrl, string $apiKey, $handler = null) {
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;
        $this->handler = $handler;
    }

    public function getRestClient()
    {
        $headers = [
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey
        ];
        $config = [
            'base_uri' => $this->baseUrl,
            'headers' => $headers,
            'http_errors' => true,
            'verify' => false,
            // Preserve the HTTP method across redirects. CloudHub answers a plain-http request with a
            // 301 to its https URL; without strict mode Guzzle would downgrade the POST to a GET and
            // the server returns "405 Method Not Allowed" (GET isn't a route). Strict redirects re-send
            // the POST to the https location, so an http base URL transparently upgrades instead of 405ing.
            'allow_redirects' => ['strict' => true]
        ];
        // Optional Guzzle handler injection (used by unit tests with a MockHandler). Null in
        // production, so behavior is unchanged.
        if ($this->handler !== null) {
            $config['handler'] = $this->handler;
        }
        return new Client($config);
    }

    public function get($url, $associative = false)
    {
        $verb = 'GET';
        $client = $this->getRestClient();
        $httpResponse = null;
        try {
            $httpResponse = $client->get($url);
        } catch (TransferException $ex) {
            throw $ex;
        }
        // $associative=true yields an assoc array for building model objects (e.g. CertificateModel);
        // the default (false) preserves the legacy behavior getCertificateAsync() relies on.
        return json_decode($httpResponse->getBody(), $associative);
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


