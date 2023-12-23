<?php

namespace Hachther\MeSomb\Operation\Payment;

use Hachther\MeSomb\Helper\PaymentData;
use Hachther\MeSomb\Operation\Signature;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Hachther\MeSomb\Helper\HandleExceptions;
use Hachther\MeSomb\Helper\RecordTransaction;
use Hachther\MeSomb\Model\Deposit as DepositModel;

class Deposit
{
    use HandleExceptions, PaymentData, RecordTransaction;

    /**
     * Deposit URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Customer phone number in the local format.
     *
     * @var int|string
     */
    protected $receiver;

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

    /**
     * Deposit Model.
     *
     * @var DepositModel
     */
    protected DepositModel $depositModel;

    /**
     * @param string $receiver receiver account (in the local phone number)
     * @param int $amount the amount of the transaction
     * @param string $service service code (MTN, ORANGE, AIRTEL, ...)
     * @param string $country country code 'CM' by default
     * @param string $currency currency of the transaction (XAF, XOF, ...) XAF by default
     * @param bool $conversion In case of foreign currently defined if you want to rely on MeSomb to convert the amount in the local currency
     */
    public function __construct(
        string $receiver,
        int $amount,
        string $service,
        string $country = 'CM',
        string $currency = 'XAF',
        bool $conversion = true,
    ) {
        $this->applicationKey = config('mesomb.app_key');
        $this->secretKey = config('mesomb.secret_key');
        $this->accessKey = config('mesomb.access_key');

        $this->generateURL();

        $this->receiver = $receiver;
        $this->amount = $amount;
        $this->service = $service;
        $this->country = $country ?? 'CM';
        $this->currency = $currency;
        $this->conversion = $conversion;
    }

    /**
     * Generate Deposit URL.
     */
    protected function generateURL(): string
    {
        $version = config('mesomb.version');
        $host = config('mesomb.host');
        $locale = App::currentLocale() == 'fr' ? 'fr' : 'en';

        return "{$host}/{$locale}/api/{$version}/payment/deposit/";
    }

    /**
     * Save Deposit bef[return description]ore request.
     *
     * @param array $data
     */
    protected function saveDeposit($data): array
    {
        $this->depositModel = DepositModel::create($data);

        return $data;
    }

    /**
     * Prep Request Data.
     */
    protected function prepareData(): array
    {
        $data = [
            'service' => $this->service,
            'amount'  => $this->amount,
            'country'  => $this->country,
            'currency'  => $this->currency,
            'conversion'  => $this->conversion,
            'receiver'=> trim($this->receiver, '+'),
            'customer'=> $this->customer,
            'location'=> $this->location,
            'product'=> $this->product,
        ];

        return array_filter($this->saveDeposit($data), fn ($val) => ! is_null($val));
    }

    /**
     * Record Deposit.
     *
     * @return void
     */
    protected function recordDeposit($response, string $nonce)
    {
        $data = Arr::only($response, ['status', 'success', 'message']);

        $this->depositModel->update($data);

        $this->recordTransaction($response, $this->depositModel, $nonce);
    }

    /**
     * Make Deposit Request.
     */
    public function pay(): DepositModel
    {
        $data = $this->prepareData();
        $data['source'] = 'Laravel/v'.\app()->version();
        $ip = request()->ip();
        if (empty($data['location'])) {
            $data['location'] = array(
                'ip' => $ip
            );
        }
        $nonce = Signature::nonceGenerator();
        $date = new \DateTime();
        $url = $this->generateURL();

        $credentials = ['accessKey' => $this->accessKey, 'secretKey' => $this->secretKey];
        $authorization = Signature::signRequest('payment', 'POST', $url, $date, $nonce, $credentials, ['content-type' => 'application/json'], $data);

        $headers = [
            'x-mesomb-date' => $date->getTimestamp(),
            'x-mesomb-nonce' => $nonce,
            'Authorization' => $authorization,
            'Content-Type' => 'application/json',
            'X-MeSomb-Application' => $this->applicationKey,
            'X-MeSomb-TrxID' => $this->depositModel->id,
        ];

        $response = Http::withHeaders($headers)
            ->timeout(config('mesomb.timeout'));
        if (!config('mesomb.ssl_verify')) {
            $response = $response->withoutVerifying();
        }
        $response = $response->post($url, $data);

        if ($response->failed()) {
            $this->handleException($response);
        }

        $this->recordDeposit($response->json(), $nonce);

        return $this->depositModel;
    }

    /**
     * Details on the customer performing the payment. This will help MeSomb to build for you analytics based on customer (Example: Top N customers)
     *
     * @param array<string, string> $customer = {'email': string, 'phone': string, 'town': string, 'region': string, 'country': string, 'first_name': string, 'last_name': string, 'address': string
     */
    public function setCustomer(array $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Location for where the transaction was done. This will help MeSomb to build for you location based analytics based on customer (Example: transactions per region)
     *
     * @param array<string, string> $location {'town': string, 'region': string, 'country': string}
     * @return void
     */
    public function setLocation(array $location): void
    {
        $this->location = $location;
    }

    /**
     * Give details on the product purchase will help for product-based analytics
     *
     * @param array $product {'id': string, 'name': string, 'category': string }
     * @return void
     */
    public function setProduct(array $product)
    {
        $this->product = $product;
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
