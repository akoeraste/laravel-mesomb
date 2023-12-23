<?php

namespace Hachther\MeSomb\Helper;

trait PaymentData
{
    /**
     * Amount to be Paid for Service.
     *
     * @var int|string
     */
    protected $amount;

    /**
     * Collect Service
     * MTN | ORANGE | AIRTEL.
     *
     * @var string
     */
    protected $service;

    /**
     * Collect country
     * CM | NE.
     *
     * @var string
     */
    protected $country;

    /**
     * Reference to add in the payment.
     *
     * @var string
     */
    protected $reference;

    /**
     * If false then extra fees will be added to the amount deducted to the customer.
     *
     * @var bool
     */
    protected $fees;

    /**
     * In case of foreign currently defined if you want to rely on MeSomb to convert the amount in the local currency
     *
     * @var bool
     */
    protected $conversion;

    /**
     * Trading Currency
     * XAF | XOF.
     *
     * @var string
     */
    protected $currency;

    /**
     * Collect Description.
     *
     * @var string
     */
    protected $message;

    /**
     * Redirect.
     *
     * @var string
     */
    protected $redirect;

    /**
     * Request ID.
     *
     * @var string
     */
    protected $request_id;

    /**
     * Customer information
     */
    protected ?array $customer = null;

    /**
     * Transaction location
     */
    protected ?array $location = null;

    /**
     * Transaction product
     */
    protected ?array $product = null;

    /**
     * Modify Payer.
     *
     * @param int|string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function payer($value)
    {
        $this->payer = trim($value, '+');

        return $this;
    }

    /**
     * Same as payer.
     *
     * @param string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function phone($value)
    {
        return $this->payer($value);
    }

    /**
     * Modify Amount.
     *
     * @param int|string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function amount($value)
    {
        $this->amount = $value;

        return $this;
    }

    /**
     * Modify Reference.
     *
     * @param string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function reference($value)
    {
        $this->reference = $value;

        return $this;
    }

    /**
     * Modify Message.
     *
     * @param string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function message($value)
    {
        $this->message = $value;

        return $this;
    }

    /**
     * Modify Currency.
     *
     * @param string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function currency($value)
    {
        $this->currency = $value;

        return $this;
    }

    /**
     * Modify Service.
     *
     * @param string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function service($value)
    {
        $this->service = $value;

        return $this;
    }

    /**
     * Modify Fees.
     *
     * @param string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function fees($value)
    {
        $this->fees = $value;

        return $this;
    }

    /**
     * Modify Request ID.
     *
     * @param string $value
     *
     * @return \Hachther\MeSomb\Payment
     */
    public function requestID($value)
    {
        $this->request_id = $value;

        return $this;
    }
}
