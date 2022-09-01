<?php

namespace Hachther\MeSomb\Helper;

use Hachther\MeSomb\Builder\PaymentBuilder;

trait HasPayments
{
    /**
     * Model Payment.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function payments()
    {
        return $this->morphMany('Hachther\MeSomb\Model\Payment', 'payable');
    }

    /**
     * Make Payment.
     *
     * @param int|string $payer
     * @param float|int  $amount
     *
     * @return Hachther\MeSomb\Builder\PaymentBuilder
     */
    public function payment($payer = null, $amount = null)
    {
        return new PaymentBuilder($this, $payer, $amount);
    }
}
