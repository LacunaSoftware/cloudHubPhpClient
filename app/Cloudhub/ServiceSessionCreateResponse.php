<?php
namespace Lacuna\Cloudhub;

use Lacuna\Cloudhub\TrustServiceAuthParametersModel;

// CloudHub 2.0.0: response of POST /api/sessions/services/{name}. Structurally identical to
// TrustServiceAuthParametersModel (serviceInfo + authUrl) — the server declares it as a subclass,
// so we mirror that here and inherit the null-safe parsing constructor.
class ServiceSessionCreateResponse extends TrustServiceAuthParametersModel {
}
