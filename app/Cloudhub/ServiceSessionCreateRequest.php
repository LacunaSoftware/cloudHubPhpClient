<?php
namespace Lacuna\Cloudhub;

// CloudHub 2.0.0: request body of POST /api/sessions/services/{name} (create a session against a
// specific, named trust service). Mirrors the server's SessionCreateBaseRequest — i.e. it is a
// SessionCreateRequest WITHOUT identifierType (the service is named in the URL path instead).
// `identifier` is optional here: omit it to start a service session with no CPF/CNPJ (the provider
// identifies the signer during its own auth flow, e.g. a QR code).
class ServiceSessionCreateRequest {

    public ?string $identifier;
    public string $redirectUri;
    public $type;
    public int $lifetimeInSeconds;
    public ?string $customState;
    public ?bool $discover;

    public function __construct(string $redirectUri, $type = null, string $identifier = null, int $lifetimeInSeconds = null, string $customState = null, bool $discover = null) {
        $this->redirectUri = $redirectUri;
        $this->type = $type;
        $this->identifier = $identifier;
        $this->lifetimeInSeconds = $lifetimeInSeconds ?? 300;
        $this->customState = $customState;
        $this->discover = $discover;
    }
}
