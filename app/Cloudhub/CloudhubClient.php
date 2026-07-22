<?php

namespace Lacuna\Cloudhub;
use Lacuna\Cloudhub\RestClient;
use Lacuna\Cloudhub\SessionCreateRequest;
use Lacuna\Cloudhub\SessionModel;
use Lacuna\Cloudhub\SignHashRequest;
use Lacuna\Cloudhub\GetServiceAvailabilityResponse;
use Lacuna\Cloudhub\CertificateModel;
use Lacuna\Cloudhub\ServiceSessionCreateRequest;
use Lacuna\Cloudhub\ServiceSessionCreateResponse;

class CloudhubClient {
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

    /**
     * CloudHub 2.0.0 - POST /api/sessions/services/{name}
     * Creates a session against a single, named trust service (e.g. "safeid"), returning that
     * service's auth parameters directly. Unlike createSessionAsync(), no CPF/CNPJ is required — the
     * provider identifies the signer during its own authentication flow (e.g. a QR code).
     * @param string $name The trust service name (path segment), e.g. IdentifierTypes-agnostic "safeid".
     * @param ServiceSessionCreateRequest $request
     * @return ServiceSessionCreateResponse
     */
    public function createServiceSessionAsync(string $name, ServiceSessionCreateRequest $request){
        $endpoint = $this->baseUrl . "api/sessions/services/" . rawurlencode($name);
        $res = $this->client->post($endpoint, $request);
        return new ServiceSessionCreateResponse($res);
    }

    public function getCertificateAsync(string $encodedSession){
        $getCertificateEndpoint = "api/sessions/certificate?session=" . $encodedSession;
        $endpoint = $this->baseUrl . $getCertificateEndpoint;
        return $this->client->get($endpoint);
    }

    public function signHashAsync(SignHashRequest $signHashRequest){
        $signHashEndpoint = "api/sessions/sign-hash";
        $endpoint = $this->baseUrl . $signHashEndpoint;
        return $this->client->post($endpoint, $signHashRequest);
    }

    /**
     * CloudHub 2.0.0 - GET /api/sessions/services/{name}/availability
     * Checks whether a given trust service is available for a signer (and, optionally, whether a
     * certificate was found for the supplied identifier).
     * @param string $name The trust service name (path segment).
     * @param string|null $identifier Optional signer identifier (e.g. a CPF/CNPJ).
     * @param string|null $identifierType Optional IdentifierTypes value (IdentifierTypes::CPF/CNPJ).
     * @return GetServiceAvailabilityResponse
     */
    public function getServiceAvailabilityAsync(string $name, string $identifier = null, $identifierType = null){
        $endpoint = $this->baseUrl . "api/sessions/services/" . rawurlencode($name) . "/availability";
        $query = array();
        if ($identifier !== null) { $query['identifier'] = $identifier; }
        if ($identifierType !== null) { $query['identifierType'] = $identifierType; }
        if (!empty($query)) { $endpoint .= "?" . http_build_query($query); }
        $res = $this->client->get($endpoint, true);
        return new GetServiceAvailabilityResponse($res);
    }

    /**
     * CloudHub 2.0.0 - GET /api/sessions/custom-state
     * Returns the custom state string that was supplied on session creation.
     * @param string $session The encoded session.
     * @return string|null
     */
    public function getCustomStateAsync(string $session){
        $endpoint = $this->baseUrl . "api/sessions/custom-state?session=" . urlencode($session);
        return $this->client->get($endpoint);
    }

    /**
     * CloudHub 2.0.0 - GET /api/v2/sessions/certificate
     * Structured certificate (content + alias + serviceName). The v1 getCertificateAsync() still
     * returns the raw base64 certificate for backward compatibility.
     * @param string $session The encoded session.
     * @return CertificateModel
     */
    public function getCertificateModelAsync(string $session){
        $endpoint = $this->baseUrl . "api/v2/sessions/certificate?session=" . urlencode($session);
        $res = $this->client->get($endpoint, true);
        return new CertificateModel($res);
    }
}
