<?php
namespace Lacuna\Cloudhub;

class SessionCreateRequest {

    public string $identifier;
    public string $redirectUri;
    public $type;
    public int $lifetimeInSeconds;
    // CloudHub 2.0.0 additions. $identifierType takes an IdentifierTypes value (CPF/CNPJ).
    // $customState is echoed back by GET /api/sessions/custom-state. $discover toggles service discovery.
    public $identifierType;
    public ?string $customState;
    public ?bool $discover;

    public function __construct(string $identifier, string $redirectUri, $type, int $lifetimeInSeconds = null, $identifierType = null, string $customState = null, bool $discover = null) {
        $this->identifier = $identifier;
        $this->redirectUri = $redirectUri;
        $this->type = $type;
        $this->lifetimeInSeconds = $lifetimeInSeconds ?? 300;
        $this->identifierType = $identifierType;
        $this->customState = $customState;
        $this->discover = $discover;
    }
}









