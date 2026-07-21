<?php
namespace Lacuna\Cloudhub;

class TrustServiceSessionTypes {
    // CloudHub 2.0.0: these are string-typed on the wire (were integers in 1.x). BREAKING: a 2.0
    // client only talks to a 2.0 server. Referencing the constants (e.g. ::SingleSignature) keeps
    // caller code unchanged; only the emitted value changes from 1 -> "SingleSignature".
    const SingleSignature = "SingleSignature";
    const MultiSignature = "MultiSignature";
    const SignatureSession = "SignatureSession";
    const AuthenticationSession = "AuthenticationSession";
}