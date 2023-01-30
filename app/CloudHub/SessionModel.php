<?php

namespace Lacuna\CloudHub;

use Exception;
use Lacuna\CloudHub\TrustServiceAuthParametersModel;


class SessionModel
{
    public array $services;

    public function __construct($data)
    {
        if (count($data['services']) !== 0) {
            foreach ($data['services'] as $key => $service) {
                $this->services[$key] = new TrustServiceAuthParametersModel($service);
            }
        }
        else {
            $this->services = array();
        }
    }
}
