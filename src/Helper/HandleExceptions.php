<?php

namespace Hachther\MeSomb\Helper;

use Illuminate\Http\Client\Response;
use Hachther\MeSomb\Exceptions\{InsufficientBalanceException,
    InvalidAmountException,
    InvalidPhoneNumberException,
    InvalidPinException,
    TimeoutException,
    ApplicationNotFoundException
};

trait HandleExceptions
{
    public $errorCodes = [
        'subscriber-insufficient-balance' => InsufficientBalanceException::class,
        'application-not-found' => ApplicationNotFoundException::class,
        'subscriber-not-found' => InvalidPhoneNumberException::class,
        'subscriber-invalid-length' => InvalidPhoneNumberException::class,
        'subscriber-invalid-secret-code' => InvalidPinException::class,
        'subscriber-invalid-min-amount' => InvalidAmountException::class,
        'subscriber-invalid-max-amount' => InvalidAmountException::class,
        'subscriber-timeout' => TimeoutException::class,
        'subscriber-internal-error' => TimeoutException::class,
    ];

    public function handleException(Response $response)
    {
        if (!config('mesomb.throw_exceptions')) {
            return;
        }

        $body = (object)$response->json();

        if (isset($this->errorCodes[$body->code])) {
            $class = $this->errorCodes[$body->code];
            throw new $class($body->detail);
        } else {
            $response->throw();
        }
    }
}
