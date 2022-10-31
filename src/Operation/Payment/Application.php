<?php

namespace Hachther\MeSomb\Operation\Payment;

use Illuminate\Support\Facades\{App, Cache, Http};
use DateTime;
use Hachther\MeSomb\Helper\SignedRequest;

class Application
{
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
    public static function checkStatus(): array
    {
        $applicationKey = config('mesomb.api_key');
        $url = self::generateURL('status/');
        $date = new DateTime();
        $nonce = "";

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

        Cache::put(config('mesomb.application_cache_key'), $response->json());

        return $response->json();
    }
}
