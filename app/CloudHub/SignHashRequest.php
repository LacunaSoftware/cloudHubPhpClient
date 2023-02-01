<?php
namespace Lacuna\CloudHub;

class SignHashRequest {
    public string $session;
    public string $hash;
    public ?string $digestAlgorithm;
    public ?string $digestAlgorithmOid;
    public ?string $certificateAlias;

    public function __construct(string $session, string $hash, string $digestAlgorithm, string $digestAlgorithmOid, string $certificateAlias) {
        $this->session = $session;
        $this->hash = $hash;
        $this->digestAlgorithm = $digestAlgorithm;
        $this->digestAlgorithmOid = $digestAlgorithmOid;
        $this->certificateAlias = $certificateAlias;
    }

}