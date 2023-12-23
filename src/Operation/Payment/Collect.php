<?php

namespace Hachther\MeSomb\Operation\Payment;

use Hachther\MeSomb\Helper\HandleExceptions;
use Hachther\MeSomb\Helper\PaymentData;
use Hachther\MeSomb\Helper\RecordTransaction;
use Hachther\MeSomb\Helper\SignedRequest;
use Hachther\MeSomb\Model\Payment as PaymentModel;
use Hachther\MeSomb\Operation\Signature;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class Collect
{
    use HandleExceptions, PaymentData, RecordTransaction;

    /**
     * MeSomb Collect URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Customer phone number in the local format.
     *
     * @var int|string
     */
    protected $payer;

    /**
     * Collect Model.
     *
     * @var null|PaymentModel
     */
    protected $paymentModel;

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
     * @param string $payer the account number to collect from
     * @param int $amount amount to collect
     * @param string $service MTN, ORANGE, AIRTEL
     * @param string $country country CM, NE
     * @param string $currency code of the currency of the amount
     * @param bool $fees if you want MeSomb to deduct he fees in the collected amount
     * @param bool $conversion In case of foreign currently defined if you want to rely on MeSomb to convert the amount in the local currency
     * @param string|null $message Message to include in the transaction
     * @param string|null $redirect Where to redirect after the payment
     */
    public function __construct(
        string $payer,
        int $amount,
        string $service,
        string $country = 'CM',
        string $currency = 'XAF',
        bool $fees = true,
        bool $conversion = true,
        ?string $message = null,
        ?string $redirect = null,
    ) {
        $this->applicationKey = config('mesomb.app_key');
        $this->secretKey = config('mesomb.secret_key');
        $this->accessKey = config('mesomb.access_key');

        $this->generateURL();

        $this->payer = trim($payer, '+');
        $this->amount = $amount;
        $this->service = $service;
        $this->country = $country ?? 'CM';
        $this->currency = $currency;
        $this->fees = $fees;
        $this->conversion = $conversion;
        $this->message = $message;
        $this->redirect = $redirect;
    }

    /**
     * Generate Collect URL.
     */
    protected function generateURL(): string
    {
        $version = config('mesomb.version');
        $host = config('mesomb.host');
        $locale = App::currentLocale() == 'fr' ? 'fr' : 'en';

        return "{$host}/{$locale}/api/{$version}/payment/collect/";
    }

    /**
     * Save Collect before request.
     *
     * @param array $data
     */
    protected function savePayment($data): array
    {
        $this->paymentModel = PaymentModel::create($data);

        $data['reference'] = $this->reference ?? $this->paymentModel->id;
        $this->request_id = $this->request_id ?? $this->paymentModel->id;

        return $data;
    }

    /**
     * Prep Request Data.
     */
    protected function prepareData(): array
    {
        $data = [
            'service' => $this->service,
            'country' => $this->country,
            'amount'  => $this->amount,
            'payer'   => $this->payer,
            'fees'    => $this->fees,
            'currency'=> $this->currency,
            'message' => $this->message,
            'redirect'=> $this->redirect,
            'customer'=> $this->customer,
            'location'=> $this->location,
            'product'=> $this->product,
        ];

        return array_filter($this->savePayment($data), fn ($val) => ! is_null($val));
    }

    /**
     * Send Collect Request.
     *
     * @return PaymentModel|null
     * @throws \Hachther\MeSomb\Exceptions\InvalidClientRequestException
     * @throws \Hachther\MeSomb\Exceptions\PermissionDeniedException
     * @throws \Hachther\MeSomb\Exceptions\ServerException
     * @throws \Hachther\MeSomb\Exceptions\ServiceNotFoundException
     */
    public function pay(): ?PaymentModel
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
            'X-MeSomb-OperationMode' => config('mesomb.mode'),
            'X-MeSomb-TrxID' => $this->paymentModel->id,
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

        $this->recordPayment($response->json(), $nonce);

        return $this->paymentModel;
    }

    /**
     * Record Response to DATABAase.
     *
     * @param array|json $response
     *
     * @return void
     */
    protected function recordPayment($response, string $nonce): void
    {
        $data = Arr::only($response, ['status', 'success', 'message']);

        $this->paymentModel->update($data);

        $this->recordTransaction($response, $this->paymentModel, $nonce);
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

    public function setApplicationKey(string $applicationKey): Collect
    {
        $this->applicationKey = $applicationKey;
        return $this;
    }

    public function setAccessKey(string $accessKey): Collect
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    public function setSecretKey(string $secretKey): Collect
    {
        $this->secretKey = $secretKey;
        return $this;
    }
}
