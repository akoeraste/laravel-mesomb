<?php

namespace Hachther\MeSomb;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Hachther\MeSomb\Model\Transaction as ModelTransaction;

class Transaction
{
    /**
     * Generate Checking URL.
     */
    public static function getURL(string $id): string
    {
        $config = config('mesomb.version');
        $key = config('mesomb.key');

        return "https://mesomb.hachther.com/api/{$config}/applications/{$key}/transactions/{$id}";
    }

    /**
     * Check Transaction sTatus.
     *
     * @param \Hachther\MeSomb\Model\Deposit|\Hachther\MeSomb\Model\Payment $model
     *
     * @return null|\Hachther\MeSomb\Model\Transaction
     */
    public static function checkStatus($model)
    {
        if (is_string($model)) {
            $id = $model;
        } elseif ($model->transaction) {
            $id = $model->transaction->pk;
        }

        if (! $id) {
            return;
        }

        $response = Http::withToken(config('mesomb.api_key'), 'Token')
            ->get(self::getURL($id));

        $response->throw();

        if ($response->successful()) {
            $data = $response->json();

            $data['ts'] = Carbon::parse($data['ts']);

            if (! is_string($model)) {
                $model->transaction()->updateOrCreate($data);

                return $model->transaction;
            } else {
                return ModelTransaction::updateOrCreate($data);
            }
        }
    }
}
