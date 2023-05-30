<?php

namespace Lacuna\Cloudhub;
class Util {
    static function base64Convert(string $string): string {
        if(!base64_decode($string, true) != false) {
            return base64_encode($string);
        } else {
            return $string;
        }
    }
}