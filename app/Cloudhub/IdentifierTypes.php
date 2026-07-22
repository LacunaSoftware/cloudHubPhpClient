<?php
namespace Lacuna\Cloudhub;

// CloudHub 2.0.0: signer identifier kind, used by SessionCreateRequest::$identifierType and the
// services/availability query. String-typed on the wire.
class IdentifierTypes {
    const CPF = "CPF";
    const CNPJ = "CNPJ";
}
