<?php

namespace Hachther\MeSomb\Operation\Payment;

use DateTime;
use Hachther\MeSomb\Helper\SignedRequest;
use Hachther\MeSomb\Model\Payment;
use Hachther\MeSomb\Operation\Signature;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Hachther\MeSomb\Model\Transaction as ModelTransaction;

class Transaction
{
    /**
     * Your service application key on MeSomb
     *
     * @var string $applicationKey
     */
    private string $applicationKey;

    /**
     * Your access key provided by MeSomb
     *
     * @var string $accessKey
     */
    private string $accessKey;

    /**
     * Your secret key provided by MeSomb
     *
     * @var string $secretKey
     */
    private string $secretKey;

    public function __construct()
    {
        $this->applicationKey = config('mesomb.app_key');
        $this->secretKey = config('mesomb.secret_key');
        $this->accessKey = config('mesomb.access_key');
    }

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
    public function checkStatus($model)
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

        $credentials = ['accessKey' => $this->accessKey, 'secretKey' => $this->secretKey];
        $authorization = Signature::signRequest('payment', 'GET', $url, $date, $nonce, $credentials);

        $headers = [
            'x-mesomb-date' => $date->getTimestamp(),
            'x-mesomb-nonce' => '',
            'Authorization' => $authorization,
            'X-MeSomb-Application' => $this->applicationKey,
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

    public function setApplicationKey(string $applicationKey): void
    {
        $this->applicationKey = $applicationKey;
    }

    public function setAccessKey(string $accessKey): void
    {
        $this->accessKey = $accessKey;
    }

    public function setSecretKey(string $secretKey): void
    {
        $this->secretKey = $secretKey;
    }
}
