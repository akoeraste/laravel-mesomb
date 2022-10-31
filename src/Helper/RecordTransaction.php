<?php

namespace Hachther\MeSomb\Helper;

use Illuminate\Support\{Arr, Carbon};
use Hachther\MeSomb\Model\Deposit;
use Hachther\MeSomb\Model\Payment;
use Hachther\MeSomb\Model\Transaction;

trait RecordTransaction
{
    protected $transactionFields = [
        'pk',
        'status',
        'amount',
        'type',
        'service',
        'message',
        'b_party',
        'fees',
        'external_id',
        'ts',
        'direction',
        'reference',
        'customer',
        'location',
        'products',
    ];

    /**
     * Extract on Fields saved in DB.
     */
    protected function extractSavableTransactionDetails(array $data): array
    {
        return Arr::only($data, $this->transactionFields);
    }

    /**
     * Save {Model} Transaction.
     *
     * @param array $data
     */
    protected function saveTransaction($data, $model, string $nonce): void
    {
        $data = $this->extractSavableTransactionDetails($data);

        $data['ts'] = Carbon::parse($data['ts']);
        $data['direction'] = (string) ($data['direction']);
        $data['customer'] = json_encode($data['customer']);
        $data['location'] = json_encode($data['location']);
        $data['products'] = json_encode($data['products']);
        $data['nonce'] = $nonce;

        $model->transaction()->updateOrCreate($data);
    }

    /**
     * Save Transaction.
     *
     * @param Deposit|Payment|Transaction $model
     */
    protected function recordTransaction(array $response, Deposit|Payment|Transaction $model, string $nonce = null): void
    {
        if (Arr::has($response, 'transaction')) {
            $transaction = Arr::get($response, 'transaction');

            $this->saveTransaction($transaction, $model, $nonce);
        }
    }
}
