<?php

namespace Hachther\MeSomb\Helper;

use Hachther\MeSomb\Operation\Signature;

class SignedRequest
{
    public static function getAuthorization($method, $url, $date, $nonce, array $headers = [], array $body = null): string
    {
        $credentials = ['accessKey' => config('mesomb.access_key'), 'secretKey' => config('mesomb.secret_key')];

        return Signature::signRequest('payment', $method, $url, $date, $nonce, $credentials, $headers, $body);
    }
}
