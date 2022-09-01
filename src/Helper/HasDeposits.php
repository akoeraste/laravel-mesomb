<?php

namespace Hachther\MeSomb\Helper;

use Hachther\MeSomb\Builder\DepositBuilder;

trait HasDeposits
{
    /**
     * Model Deposits.
     *
     * @return Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function deposits()
    {
        return $this->morphMany('Hachther\MeSomb\Model\Deposit', 'depositable');
    }

    /**
     * Make Deposit.
     *
     * @param int|string $receiver
     * @param float|int  $amount
     *
     * @return Hachther\MeSomb\Builder\DepositBuilder
     */
    public function deposit($receiver = null, $amount = null)
    {
        return new DepositBuilder($this, $receiver, $amount);
    }
}
