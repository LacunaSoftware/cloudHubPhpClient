<?php
namespace Lacuna\Cloudhub;
use Lacuna\Cloudhub\TrustServiceInfoModel;

class TrustServiceAuthParametersModel {
    public TrustServiceInfoModel $serviceInfo;
    // CloudHub 2.0.0 spec marks `authUrl` nullable; guard to avoid a null-to-string TypeError.
    public ?string $authUrl;

    public function __construct($data) {
        $this->serviceInfo = new TrustServiceInfoModel($data['serviceInfo']);
        $this->authUrl = isset($data['authUrl']) ? $data['authUrl'] : null;
    }
}