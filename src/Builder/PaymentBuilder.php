<?php

namespace Hachther\MeSomb\Builder;

use Hachther\MeSomb\Helper\PaymentData;
use Hachther\MeSomb\Payment;

class PaymentBuilder
{
    use PaymentData;

    /**
     * Payment Owner Model.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    public function __construct(
        $owner,
        $payer,
        $amount,
        $service = null,
        $currency = 'XAF',
        $fees = true,
        $message = null,
        $redirect = null
    ) {
        $this->owner = $owner;
        $this->payer = $payer;
        $this->amount = $amount;
        $this->service = $service;
        $this->fees = $fees;
        $this->currency = $currency;
        $this->message = $message;
        $this->redirect = $redirect;
    }

    /**
     * Make Model Payment.
     *
     * @return Hachther\MeSomb\Model\Payment
     */
    public function pay()
    {
        $payment = (new Payment(
            $this->payer,
            $this->amount,
            $this->service,
            $this->currency,
            $this->fees,
            $this->message,
            $this->redirect
        ))->pay();

        $this->owner->payments()->save($payment);

        return $payment;
    }
}
