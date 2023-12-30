<?php

namespace Hachther\MeSomb\Builder;

use Hachther\MeSomb\Helper\PaymentData;
use Hachther\MeSomb\Operation\Payment\Collect;

class PaymentBuilder
{
    use PaymentData;

    /**
     * Collect Owner Model.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    public function __construct(
        $owner,
        $payer,
        $amount,
        $service = null,
        $country = 'CM',
        $currency = 'XAF',
        $fees = true,
        $conversion = true,
        $message = null,
        $redirect = null
    ) {
        $this->owner = $owner;
        $this->payer = $payer;
        $this->amount = $amount;
        $this->service = $service;
        $this->fees = $fees;
        $this->country = $country;
        $this->currency = $currency;
        $this->conversion = $conversion;
        $this->message = $message;
        $this->redirect = $redirect;
    }

    /**
     * Make Model Collect.
     *
     * @return \Hachther\MeSomb\Model\Payment
     */
    public function pay()
    {
        $payment = (new Collect(
            $this->payer,
            $this->amount,
            $this->service,
            $this->country,
            $this->currency,
            $this->fees,
            $this->conversion,
            $this->message,
            $this->redirect
        ))->pay();

        $this->owner->payments()->save($payment);

        return $payment;
    }
}
