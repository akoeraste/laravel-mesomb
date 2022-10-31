<?php

namespace Hachther\MeSomb\Operation\Payment;

use DateTime;
use Hachther\MeSomb\Helper\SignedRequest;
use Hachther\MeSomb\Model\Payment;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Hachther\MeSomb\Model\Transaction as ModelTransaction;

class Transaction
{
    /**
     * Generate Checking URL.
     */
    public static function generateURL(string $endpoint, array $ids): string
    {
        $version = config('mesomb.version');
        $host = config('mesomb.host');
        $locale = App::currentLocale() == 'fr' ? 'fr' : 'en';
        $query = implode(",", $ids);

        return "{$host}/{$locale}/api/{$version}/payment/{$endpoint}?ids=$query";
    }

    /**
     * Check Transaction status.
     *
     * @param \Hachther\MeSomb\Model\Deposit|Payment $model
     *
     * @return array
     * @throws RequestException
     */
    public static function checkStatus($model)
    {
        $ids = [];
        if (is_string($model)) {
            $ids[] = $model;
        } elseif ($model->transaction) {
            $ids[] = $model->transaction->pk;
        } elseif (is_array($model)) {
            $ids = $model;
        }

        if (empty($ids)) {
            return;
        }

        $url = self::generateURL('transactions/', $ids);
        $date = new DateTime();
        $nonce = "";
        $applicationKey = config('mesomb.api_key');

        $authorization = SignedRequest::getAuthorization('GET', $url, $date, $nonce);

        $headers = [
            'x-mesomb-date' => $date->getTimestamp(),
            'x-mesomb-nonce' => '',
            'Authorization' => $authorization,
            'X-MeSomb-Application' => $applicationKey,
        ];

        $response = Http::withHeaders($headers)
            ->get($url);

        $response->throw();

        $result = [];
        if ($response->successful()) {
            $transactions = $response->json();
            foreach ($transactions as $data) {
                $data['ts'] = Carbon::parse($data['ts']);

                if (!is_string($model)) {
                    $model->transaction()->updateOrCreate($data);
                    $result[] = $model->transaction;
                } else {
                    $result[] = ModelTransaction::updateOrCreate($data);
                }
            }
        }
        return $result;
    }
}
