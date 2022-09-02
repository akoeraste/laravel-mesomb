<?php

namespace Hachther\MeSomb;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Hachther\MeSomb\Helper\{HandleExceptions, RecordTransaction};
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
     * @var \Hachther\MeSomb\Deposit
     */
    protected $depositModel;

    public function __construct($receiver, $amount, $service = null)
    {
        $this->generateURL();

        $this->receiver = $receiver;
        $this->amount = $amount;
        $this->service = $service;
    }

    /**
     * Generate Deposit URL.
     */
    protected function generateURL(): void
    {
        $version = config('mesomb.version');
        $key = config('mesomb.key');

        $this->url = "https://mesomb.hachther.com/api/{$version}/applications/{$key}/deposit/";
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
            'receiver'=> trim($this->receiver, '+'),
        ];

        return array_filter($this->saveDeposit($data));
    }

    /**
     * Record Deposit.
     *
     * @return void
     */
    protected function recordDeposit($response)
    {
        $data = Arr::only($response, ['status', 'success', 'message']);

        $this->depositModel->update($data);

        $this->recordTransaction($response, $this->depositModel);
    }

    /**
     * Make Deposit Request.
     */
    public function pay(): DepositModel
    {
        $data = $this->prepareData();

        $response = Http::withToken(config('mesomb.api_key'), 'Token')
            ->post($this->url, $data);

        $this->recordDeposit($response->json());

        if ($response->failed()) {
            $this->handleException($response);
        }

        return $this->depositModel;
    }
}
