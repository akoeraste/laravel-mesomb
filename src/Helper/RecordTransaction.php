<?php

namespace Hachther\MeSomb\Helper;

use Illuminate\Support\{Arr, Carbon};

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
        'product',
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
    protected function saveTransaction($data, $model): void
    {
        $data = $this->extractSavableTransactionDetails($data);

        $data['ts'] = Carbon::parse($data['ts']);
        $data['direction'] = (string) ($data['direction']);
        $data['customer'] = json_encode($data['customer']);
        $data['location'] = json_encode($data['location']);
        $data['product'] = json_encode($data['product']);

        $model->transaction()->updateOrCreate($data);
    }

    /**
     * Save Transaction.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    protected function recordTransaction(array $response, $model): void
    {
        if (Arr::has($response, 'transaction')) {
            $transaction = Arr::get($response, 'transaction');

            $this->saveTransaction($transaction, $model);
        }
    }
}
