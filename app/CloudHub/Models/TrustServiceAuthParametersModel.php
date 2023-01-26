<?php
namespace Lacuna\CloudHub\Models;
use Lacuna\CloudHub\Models\TrustServiceInfoModel;

class TrustServiceAuthParametersModel {
    public TrustServiceInfoModel $serviceInfo;
    public string $authUrl;

    public function __construct($data) {
        $this->serviceInfo = new TrustServiceInfoModel($data['serviceInfo']);
        $this->authUrl = $data['authUrl'];
    }
}