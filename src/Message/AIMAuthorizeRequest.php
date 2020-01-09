<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Authorize.Net AIM Authorize Request
 */
class AIMAuthorizeRequest extends AIMAbstractRequest
{
    const MARKET_TYPE_ECOMMERCE  = '0';
    const MARKET_TYPE_MOTO       = '1';
    const MARKET_TYPE_RETAIL     = '2';

    const DEVICE_TYPE_UNKNOWN = '1';
    const DEVICE_TYPE_UNATTENDED_TERMINAL = '2';
    const DEVICE_TYPE_SELF_SERVICE_TERMINAL = '3';
    const DEVICE_TYPE_ELECTRONIC_CASH_REGISTER = '4';
    const DEVICE_TYPE_PC_BASED_TERMINAL = '5';
    const DEVICE_TYPE_AIRPAY = '6';
    const DEVICE_TYPE_WIRELESS_POS = '7';
    const DEVICE_TYPE_WEBSITE = '8';
    const DEVICE_TYPE_DIAL_TERMINAL = '9';
    const DEVICE_TYPE_VIRTUAL_TERMINAL = '10';

    protected $action = 'authOnlyTransaction';

    const ECHECK = "ECHECK";
    const CREDIT_CARD = "CC";

    public function getData()
    {
        $this->validate('amount');

        $data = $this->getBaseData();
        $data['x_customer_ip'] = $this->getClientIp();
        $data['x_cust_id'] = $this->getCustomerId();

        if ($card = $this->getCard()) {
            $card->validate();
            $data['x_card_num'] = $this->getCard()->getNumber();
            $data['x_exp_date'] = $this->getCard()->getExpiryDate('my');
            $data['x_card_code'] = $this->getCard()->getCvv();
            $data['x_method'] = self::CREDIT_CARD;
        } elseif ($bankAccount = $this->getBankAccount()) {
            $bankAccount->validate();
            $data['x_bank_aba_code'] = $this->getBankAccount()->getRoutingNumber();
            $data['x_bank_acct_num'] = $this->getBankAccount()->getAccountNumber();
            $data['x_bank_acct_type'] = $this->getBankAccount()->getBankAccountType();
            $data['x_bank_name'] = $this->getBankAccount()->getBankName();
            $data['x_bank_acct_name'] = $this->getBankAccount()->getName();
            $data['x_echeck_type'] = "WEB";
            $data['x_recurring_billing'] = "FALSE";//NEED when we set echeck_type is WEB or TEL
            $data['x_method'] = self::ECHECK;
        }

        $data->transactionRequest->amount = $this->getAmount();
        $this->addPayment($data);
        $this->addSolutionId($data);
        $this->addBillingData($data);
        $this->addCustomerIP($data);
        $this->addRetail($data);
        $this->addTransactionSettings($data);

        return $data;
    }

    protected function addPayment(\SimpleXMLElement $data)
    {
        /**
         * @link http://developer.authorize.net/api/reference/features/acceptjs.html Documentation on opaque data
         */
        if ($this->getOpaqueDataDescriptor() && $this->getOpaqueDataValue()) {
            $data
                ->transactionRequest
                ->payment
                ->opaqueData
                ->dataDescriptor = $this->getOpaqueDataDescriptor();

            $data->transactionRequest
                ->payment
                ->opaqueData
                ->dataValue = $this->getOpaqueDataValue();

            return;
        }

        // Try trackData first.

        $creditCard = $this->getCard();

        if (($track1 = $creditCard->getTrack1())
            && ($track2 = $creditCard->getTrack2())
        ) {
            $data
                ->transactionRequest
                ->payment
                ->trackData
                ->track1 = $track1;

            $data
                ->transactionRequest
                ->payment
                ->trackData
                ->track2 = $track2;
        } else {
            // Validate the standard credit card number.
            $this->validate('card');

            /** @var CreditCard $card */
            $card = $this->getCard();
            $card->validate();
            $data
                ->transactionRequest
                ->payment
                ->creditCard
                ->cardNumber = $card->getNumber();
            $data
                ->transactionRequest
                ->payment
                ->creditCard
                ->expirationDate = $card->getExpiryDate('my');

            if (!empty($card->getCvv())) {
                $data->transactionRequest->payment->creditCard->cardCode = $card->getCvv();
            }
        }
    }

    protected function addCustomerIP(\SimpleXMLElement $data)
    {
        $ip = $this->getClientIp();
        if (!empty($ip)) {
            $data->transactionRequest->customerIP = $ip;
        }
    }

    protected function addRetail(\SimpleXMLElement $data)
    {
        $deviceType = $this->getDeviceType();
        $marketType = $this->getMarketType();

        if (!isset($deviceType) && !isset($marketType)) {
            return;
        }

        if (!isset($deviceType) && isset($marketType)) {
            throw new InvalidRequestException("deviceType is required if marketType is set");
        }

        if (isset($deviceType) && !isset($marketType)) {
            $marketType = static::MARKET_TYPE_RETAIL;
        }

        if (!in_array($deviceType, [
            static::DEVICE_TYPE_UNKNOWN,
            static::DEVICE_TYPE_UNATTENDED_TERMINAL,
            static::DEVICE_TYPE_SELF_SERVICE_TERMINAL,
            static::DEVICE_TYPE_ELECTRONIC_CASH_REGISTER,
            static::DEVICE_TYPE_PC_BASED_TERMINAL,
            static::DEVICE_TYPE_AIRPAY,
            static::DEVICE_TYPE_WIRELESS_POS,
            static::DEVICE_TYPE_WEBSITE,
            static::DEVICE_TYPE_DIAL_TERMINAL,
            static::DEVICE_TYPE_VIRTUAL_TERMINAL,
        ])) {
            throw new InvalidRequestException("deviceType `{$deviceType}` is invalid");
        }

        if (!in_array($marketType, [
            static::MARKET_TYPE_ECOMMERCE,
            static::MARKET_TYPE_MOTO,
            static::MARKET_TYPE_RETAIL,
        ])) {
            throw new InvalidRequestException("marketType `{$marketType}` is invalid");
        }

        $data->transactionRequest->retail->marketType = $marketType;
        $data->transactionRequest->retail->deviceType = $deviceType;
    }

    public function getDeviceType()
    {
        return $this->getParameter('deviceType');
    }

    public function setDeviceType($value)
    {
        return $this->setParameter('deviceType', $value);
    }

    public function getMarketType()
    {
        return $this->getParameter('marketType');
    }

    public function setMarketType($value)
    {
        return $this->setParameter('marketType', $value);
    }
}
