<?php
namespace Lacuna\Cloudhub;

class SessionCreateRequest {
    
    public ?string $identifier;
    public ?string $redirectUri;
    public $type;

    public function __construct(string $identifier, string $redirectUri, $type) {
        $this->identifier = $identifier;
        $this->redirectUri = $redirectUri;
        $this->type = $type;
    }
}









