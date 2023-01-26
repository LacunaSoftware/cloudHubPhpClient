<?php
namespace Lacuna\CloudHub\Models;

class TrustServiceInfoModel {
    public string $serviceName;
    public string $provider;
    public string $endpoint;
    public string $badgeUrl;

    public function __construct($data) {
        $this->serviceName = $data['serviceName'];
        $this->provider = $data['provider'];
        $this->endpoint = $data['endpoint'];
        $this->badgeUrl = $data['badgeUrl'];
    }

}