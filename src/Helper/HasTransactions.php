<?php

namespace Hachther\MeSomb\Helper;

use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasTransactions
{
    /**
     * Deposit|Collect Transaction.
     */
    public function transaction(): MorphOne
    {
        return $this->morphOne('Hachther\MeSomb\Model\Transaction', 'transacable');
    }

    /**
     * Succesful Transactoin.
     */
    public function toggleToSuccess(): void
    {
        $this->update(['success' => true]);

        $this->save();
    }
}
