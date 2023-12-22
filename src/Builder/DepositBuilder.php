<?php

namespace Hachther\MeSomb\Builder;

use Hachther\MeSomb\Operation\Payment\Deposit;
use Hachther\MeSomb\Helper\DepositData;

class DepositBuilder
{
    use DepositData;

    /**
     * Collect Owner Model.
     *
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $owner;

    public function __construct($owner, $receiver, $amount, $service = null)
    {
        $this->owner = $owner;

        $this->receiver = $receiver;
        $this->amount = $amount;
        $this->service = $service;
    }

    /**
     * Make Deposit.
     *
     * @return \Hachther\MeSomb\Model\Deposit
     */
    public function pay()
    {
        $deposit = (new Deposit(
            $this->receiver,
            $this->amount,
            $this->service
        ))->pay();

        $this->owner->deposits()->save($deposit);

        return $deposit;
    }
}
