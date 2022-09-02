<?php

namespace Hachther\MeSomb;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Hachther\MeSomb\Helper\HandleExceptions;
use Hachther\MeSomb\Helper\PaymentData;
use Hachther\MeSomb\Helper\RecordTransaction;
use Hachther\MeSomb\Model\Payment as PaymentModel;

class Payment
{
    use HandleExceptions, PaymentData, RecordTransaction;

    /**
     * MeSomb Payment Payment URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Payment Model.
     *
     * @var null|\Hachther\MeSomb\Model\Payment
     */
    protected $paymentModel;


    public function __construct(
        $payer,
        $amount,
        $service,
        $country = 'CM',
        $currency = 'XAF',
        $fees = true,
        $message = null,
        $redirect = null,
    ) {
        $this->generateURL();

        $this->payer = trim($payer, '+');
        $this->amount = $amount;
        $this->service = $service;
        $this->country = $country ?? 'CM';
        $this->currency = $currency;
        $this->fees = $fees;
        $this->message = $message;
        $this->redirect = $redirect;
    }

    /**
     * Generate Payment URL.
     */
    protected function generateURL(): void
    {
        $version = config('mesomb.version');

        $this->url = "https://mesomb.hachther.com/api/{$version}/payment/online/";
    }

    /**
     * Save Payment before request.
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
     * Send Payment Request.
     *
     * @return \Hachther\MeSomb\Model\Payment
     */
    public function pay()
    {
        $data = $this->prepareData();

        $headers = [
            'X-MeSomb-Application'   => config('mesomb.key'),
            'X-MeSomb-RequestId'     => $this->request_id,
            'X-MeSomb-OperationMode' => config('mesomb.mode'),
        ];

        $response = Http::withHeaders($headers)
            ->post($this->url, $data);

        $this->recordPayment($response->json());

        if ($response->failed()) {
            $this->handleException($response);
        }

        return $this->paymentModel;
    }

    /**
     * Record Response to DATABAase.
     *
     * @param array|json $response
     *
     * @return void
     */
    protected function recordPayment($response)
    {
        $data = Arr::only($response, ['status', 'success', 'message']);

        $this->paymentModel->update($data);

        $this->recordTransaction($response, $this->paymentModel);
    }

    /**
     * Details on the customer performing the payment. This will help MeSomb to build for you analytics based on customer (Example: Top N customers)
     *
     * @param array $customer = [
     *  'email' => string,
     *  'phone' => string,
     *  'town' => string,
     *  'region' => string,
     *  'country' => string, // Country code of the country
     *  'first_name' => string,
     *  'last_name' => string,
     *  'address' => string
     * ]
     */
    public function setCustomer(array $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Location for where the transaction was done. This will help MeSomb to build for you location based analytics based on customer (Example: transactions per region)
     *
     * @param array $location = [
     *  'town' => string,
     *  'region' => string,
     *  'country' => string //country code
     * ]
     * @return void
     */
    public function setLocation(array $location): void
    {
        $this->location = $location;
    }

    /**
     * Give details on the product purchase will help for product-based analytics
     *
     * @param array $product = [
     *  'id' => string,
     *  'name' => string,
     *  'category' => string
     * ]
     * @return void
     */
    public function setProduct(array $product)
    {
        $this->product = $product;
    }
}
