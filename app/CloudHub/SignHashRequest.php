<?php
namespace Lacuna\CloudHub;

class SignHashRequest {
    public string $session;
    public string $hash;
    public ?string $digestAlgorithm = null;
    public ?string $digestAlgorithmOid = null;
    public ?string $certificateAlias = null;

    public function __construct(string $session, string $hash, string $digestAlgorithm = null, string $digestAlgorithmOid = null, string $certificateAlias = null) {
        $this->session = $session;
        $this->hash = $hash;
        $this->digestAlgorithm = $digestAlgorithm;
        $this->digestAlgorithmOid = $digestAlgorithmOid;
        $this->certificateAlias = $certificateAlias;
    }
}