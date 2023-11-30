<?php

namespace Hachther\MeSomb\Helper;

use Hachther\MeSomb\Builder\PaymentBuilder;

trait HasPayments
{
    /**
     * Model Collect.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function payments()
    {
        return $this->morphMany('Hachther\MeSomb\Model\Payment', 'payable');
    }

    /**
     * Make Collect.
     *
     * @param int|string $payer
     * @param float|int  $amount
     *
     * @return PaymentBuilder
     */
    public function payment($payer = null, $amount = null)
    {
        return new PaymentBuilder($this, $payer, $amount);
    }
}
