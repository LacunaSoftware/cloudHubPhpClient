<?php
namespace Lacuna\Cloudhub;

class SessionCreateRequest {

    // `identifier` (the signer's CPF/CNPJ) is OPTIONAL on the server, so it is nullable here too:
    // pass null to start a session with no identifier (e.g. list every configured service, or let a
    // named service identify the signer via its own auth/QR flow). Still the first positional arg,
    // so existing callers that pass a string keep working unchanged.
    public ?string $identifier;
    public string $redirectUri;
    public $type;
    public int $lifetimeInSeconds;
    // CloudHub 2.0.0 additions. $identifierType takes an IdentifierTypes value (CPF/CNPJ).
    // $customState is echoed back by GET /api/sessions/custom-state. $discover toggles service discovery.
    public $identifierType;
    public ?string $customState;
    public ?bool $discover;

    public function __construct(?string $identifier, string $redirectUri, $type, int $lifetimeInSeconds = null, $identifierType = null, string $customState = null, bool $discover = null) {
        $this->identifier = $identifier;
        $this->redirectUri = $redirectUri;
        $this->type = $type;
        $this->lifetimeInSeconds = $lifetimeInSeconds ?? 300;
        $this->identifierType = $identifierType;
        $this->customState = $customState;
        $this->discover = $discover;
    }
}









