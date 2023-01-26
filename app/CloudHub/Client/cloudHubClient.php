<?php

namespace Lacuna\CloudHub\Client;

use Lacuna\CloudHub\Client\RestClient;
use Lacuna\CloudHub\Models\SessionCreateRequest;
use Lacuna\CloudHub\Models\SessionModel;
use Lacuna\CloudHub\Models\SignHashRequest;

class CloudHubClient {
    private $baseUrl;
    private RestClient $client;
    
    public function __construct($baseUrl, $apiKey) {
        $this->baseUrl = $baseUrl;
        $this->client = new RestClient($baseUrl, $apiKey);
    }

    public function createSessionAsync(SessionCreateRequest $sessionCreateRequest){
        $createSessionEndpoint = "api/sessions/";
        $endpoint = $this->baseUrl . $createSessionEndpoint;
        $res = $this->client->post($endpoint, $sessionCreateRequest);
        return new SessionModel($res);
    }

    public function getCertificateAsync(string $encodedSession){
        $getCertificateEndpoint = "api/sessions/certificate?session=" . $encodedSession;
        $endpoint = $this->baseUrl . $getCertificateEndpoint;
        return $this->client->get($endpoint, $encodedSession);
    }

    public function signHashAsync(SignHashRequest $signHashRequest){
        $signHashEndpoint = "api/sessions/sign-hash";
        $endpoint = $this->baseUrl . $signHashEndpoint;
        return $this->client->post($endpoint, $signHashRequest);
    }
}
