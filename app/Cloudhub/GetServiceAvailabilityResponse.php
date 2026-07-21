<?php
namespace Lacuna\Cloudhub;

// CloudHub 2.0.0: response of GET /api/sessions/services/{name}/availability.
class GetServiceAvailabilityResponse {
    public bool $discoveryAvailable;
    public ?bool $certificateFound;

    public function __construct($data) {
        $this->discoveryAvailable = isset($data['discoveryAvailable']) ? $data['discoveryAvailable'] : false;
        $this->certificateFound = isset($data['certificateFound']) ? $data['certificateFound'] : null;
    }
}
