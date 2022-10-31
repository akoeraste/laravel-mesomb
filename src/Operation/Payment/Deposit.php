<?php

namespace Hachther\MeSomb\Operation\Payment;

use Hachther\MeSomb\Operation\Signature;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Hachther\MeSomb\Helper\HandleExceptions;
use Hachther\MeSomb\Helper\RecordTransaction;
use Hachther\MeSomb\Helper\SignedRequest;
use Hachther\MeSomb\Model\Deposit as DepositModel;

class Deposit
{
    use HandleExceptions, RecordTransaction;

    /**
     * Deposit URL.
     *
     * @var string
     */
    protected $url;

    /**
     * Reference to add in the payment.
     *
     * @var string
     */
    protected $pin;

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

        $data['pin'] = config('mesomb.pin');

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
        ];

        return array_filter($this->saveDeposit($data));
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
        $nonce = Signature::nonceGenerator();
        $date = new \DateTime();
        $url = $this->generateURL();

        $authorization = SignedRequest::getAuthorization('POST', $url, $date, $nonce, ['content-type' => 'application/json'], $data);

        $headers = [
            'x-mesomb-date' => $date->getTimestamp(),
            'x-mesomb-nonce' => $nonce,
            'Authorization' => $authorization,
            'Content-Type' => 'application/json',
            'X-MeSomb-Application' => config('mesomb.key'),
            'X-MeSomb-TrxID' => $this->depositModel->id,
        ];

        $response = Http::withHeaders($headers)
            ->post($url, $data);


        if ($response->failed()) {
            $this->handleException($response);
        }

        $this->recordDeposit($response->json(), $nonce);

        return $this->depositModel;
    }
}
