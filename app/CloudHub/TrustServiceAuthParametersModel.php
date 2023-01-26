<?php
namespace Lacuna\CloudHub;
use Lacuna\CloudHub\TrustServiceInfoModel;

class TrustServiceAuthParametersModel {
    public TrustServiceInfoModel $serviceInfo;
    public string $authUrl;

    public function __construct($data) {
        $this->serviceInfo = new TrustServiceInfoModel($data['serviceInfo']);
        $this->authUrl = $data['authUrl'];
    }
}