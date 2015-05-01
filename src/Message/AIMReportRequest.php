<?php

namespace Omnipay\AuthorizeNet\Message;

/**
 * Authorize.Net AIM Report Request
 */
class AIMReportRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://api.authorize.net/xml/v1/request.api';
    protected $developerEndpoint = 'https://apitest.authorize.net/xml/v1/request.api';

    protected $reportAction = '';

    /**
     * @return string
     */
    public function getReportAction()
    {
        return $this->reportAction;
    }

    /**
     * @param string $reportAction
     */
    public function setReportAction($reportAction)
    {
        $this->reportAction = $reportAction;
    }

    /**
     * @return \DOMDocument
     */
    public function getData()
    {
        $xmlDoc = new \DOMDocument();

        $mainNode = $xmlDoc->createElement($this->getReportAction());
        $mainNode->setAttribute('xmlns','AnetApi/xml/v1/schema/AnetApiSchema.xsd');
        $root = $xmlDoc->appendChild($mainNode);
        $mainNode->appendChild($this->getMerchantAuthenticationElement($xmlDoc));

        return $xmlDoc;
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $data->saveXML())->send();
        return $this->response = new AIMReportResponse($this, $httpResponse->getBody());
    }


    /**
     * Returns the merchant authentication element
     * @return \DOMElement
     */
    protected function getMerchantAuthenticationElement(\DOMDocument $domDocument)
    {
        $merchantAuthentication = $domDocument->createElement('merchantAuthentication');
        $merchantAuthentication->appendChild($domDocument->createElement('name', $this->getApiLoginId()));
        $merchantAuthentication->appendChild($domDocument->createElement('transactionKey', $this->getTransactionKey()));

        return $merchantAuthentication;
    }
}
