<?php

require '../app/bootstrap.php';

use CloudHub\Client\CloudHubClient;
use CloudHub\Models\SessionCreateRequest;
use CloudHub\Models\TrustServiceAuthParametersModel;
use CloudHub\Models\TrustServiceInfoModel;
use CloudHub\Models\TrustServiceSessionTypes;

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