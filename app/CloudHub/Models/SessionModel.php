<?php
namespace Lacuna\CloudHub\Models;
use Lacuna\CloudHub\Models\TrustServiceAuthParametersModel;


class SessionModel {
    public ?array $services;

    public function __construct($data) {
        foreach ($data['services'] as $key=>$service) {
            $this->services[$key] = new TrustServiceAuthParametersModel($service);
        }
    }
}