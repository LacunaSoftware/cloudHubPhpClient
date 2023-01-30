<?php

namespace Lacuna\CloudHub;

use Exception;
use Lacuna\CloudHub\TrustServiceAuthParametersModel;


class SessionModel
{
    public array $services = array();

    public function __construct($data)
    {
        if (!$data['services']) {
            foreach ($data['services'] as $key => $service) {
                $this->services[$key] = new TrustServiceAuthParametersModel($service);
            }
        }
    }
}
