<?php

namespace Hachther\MeSomb\Operation\Payment;

use Illuminate\Support\Facades\{App, Cache, Http};
use DateTime;
use Hachther\MeSomb\Helper\SignedRequest;
use Hachther\MeSomb\Operation\Signature;

class Application
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

    public function __construct() {
        $this->applicationKey = config('mesomb.app_key');
        $this->secretKey = config('mesomb.secret_key');
        $this->accessKey = config('mesomb.access_key');
    }

    /**
     * Generate Deposit URL.
     *
     * @return void
     */
    protected static function generateURL(string $endpoint): string
    {
        $version = config('mesomb.version');
        $host = config('mesomb.host');
        $locale = App::currentLocale() == 'fr' ? 'fr' : 'en';

        return "{$host}/{$locale}/api/{$version}/payment/{$endpoint}";
    }

    /**
     * Get Cached Application Status | if null request fresh copy of Application Status.
     *
     * @return array|json
     */
    public static function status()
    {
        if (Cache::has(config('mesomb.application_cache_key'))) {
            return Cache::get(config('mesomb.application_cache_key'));
        } else {
            return self::checkStatus();
        }
    }

    /**
     * Fetch Application Status.
     *
     * @return array
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function checkStatus(): array
    {
        $url = self::generateURL('status/');
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

        Cache::put(config('mesomb.application_cache_key'), $response->json());

        return $response->json();
    }
}
