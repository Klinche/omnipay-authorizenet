<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Authorize.Net AIMSettledTransactionReportResponse
 */
class AIMSettledTransactionReportResponse extends AIMReportResponse
{

    protected $resultCode = null;

    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);

        $dataString = str_replace('xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"', '', $data);

        $doc = new \DOMDocument();
        $doc->loadXML($dataString);

        $doc->formatOutput = true;

        $resultCodes = $doc->getElementsByTagName('resultCode');
        $resultCode = $resultCodes->item(0);

        $this->resultCode = $resultCode->textContent;

        $messages = $doc->getElementsByTagName('message');
    }


    public function isSuccessful()
    {
        if(strtolower($this->resultCode) == 'error') {
            return false;
        }
        return true;
    }
}
