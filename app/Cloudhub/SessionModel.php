<?php

namespace Lacuna\Cloudhub;

use Exception;
use Lacuna\Cloudhub\TrustServiceAuthParametersModel;


class SessionModel
{
    public array $services;

    public function __construct($data)
    {
        // CloudHub 2.0.0 spec marks `services` nullable, so the server may return null or omit it.
        // Guard before iterating to avoid a PHP 8 `count(): ... null given` TypeError.
        $this->services = array();
        if (isset($data['services']) && is_array($data['services'])) {
            foreach ($data['services'] as $key => $service) {
                $this->services[$key] = new TrustServiceAuthParametersModel($service);
            }
        }
    }
}
