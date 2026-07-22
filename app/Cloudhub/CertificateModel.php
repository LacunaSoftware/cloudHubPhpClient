<?php
namespace Lacuna\Cloudhub;

// CloudHub 2.0.0: response of GET /api/v2/sessions/certificate. `serviceName` was added in 2.0.
class CertificateModel {
    public ?string $content;
    public ?string $alias;
    public ?string $serviceName;

    public function __construct($data) {
        $this->content = isset($data['content']) ? $data['content'] : null;
        $this->alias = isset($data['alias']) ? $data['alias'] : null;
        $this->serviceName = isset($data['serviceName']) ? $data['serviceName'] : null;
    }
}
