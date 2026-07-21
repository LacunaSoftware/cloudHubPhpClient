<?php
namespace Lacuna\Cloudhub;

class TrustServiceInfoModel {
    // CloudHub 2.0.0 spec marks all four fields nullable. In particular `badgeUrl` is commonly null
    // (the PkiSuiteSamples discover page renders an "Empty BadgeUrl" fallback for it), so these must
    // be nullable + isset-guarded — assigning null to a non-nullable typed property is a fatal
    // TypeError on PHP 7.4+.
    public ?string $serviceName;
    public ?string $provider;
    public ?string $endpoint;
    public ?string $badgeUrl;

    public function __construct($data) {
        $this->serviceName = isset($data['serviceName']) ? $data['serviceName'] : null;
        $this->provider = isset($data['provider']) ? $data['provider'] : null;
        $this->endpoint = isset($data['endpoint']) ? $data['endpoint'] : null;
        $this->badgeUrl = isset($data['badgeUrl']) ? $data['badgeUrl'] : null;
    }

}