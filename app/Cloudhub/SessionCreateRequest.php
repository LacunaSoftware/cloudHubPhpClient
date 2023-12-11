<?php
namespace Lacuna\Cloudhub;

class SessionCreateRequest {
    
    public string $identifier;
    public string $redirectUri;
    public $type;
    public int $lifetimeInSeconds;

    public function __construct(string $identifier, string $redirectUri, $type, int $lifetimeInSeconds = null) {
        $this->identifier = $identifier;
        $this->redirectUri = $redirectUri;
        $this->type = $type;
        $this->lifetimeInSeconds = $lifetimeInSeconds ?? 300;
    }
}









