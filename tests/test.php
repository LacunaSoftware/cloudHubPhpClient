<?php

require '../app/bootstrap.php';

use Lacuna\CloudHub\Client\CloudHubClient;
use Lacuna\CloudHub\Models\SessionCreateRequest;
use Lacuna\CloudHub\Models\TrustServiceAuthParametersModel;
use Lacuna\CloudHub\Models\TrustServiceInfoModel;
use Lacuna\CloudHub\Models\TrustServiceSessionTypes;

function run(){
    $claudioRubens = new CloudHubClient("https://cloudhub.lacunasoftware.com/", "mR1j0v7L12lBHnxpgxVkIdikCN9Gm89rn8I9qet3UHo=");
    $identifier = "70380599473";
    $redirectUri = "http://localhost:3000/authentication-cloudhub-sdk/session-result";
    $createSessionRequest = new SessionCreateRequest($identifier, $redirectUri, TrustServiceSessionTypes::SingleSignature);
    return $claudioRubens->createSessionAsync($createSessionRequest);
}
$res = run();

var_dump($res);
var_dump($res->services);

// foreach ($res->services as $value) {
//     echo($value->serviceInfo->serviceName);
// }