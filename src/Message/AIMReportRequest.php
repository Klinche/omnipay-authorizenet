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

    public function getData()
    {
        $data = array();
        $data[$this->getReportAction()] = array();
        $data[$this->getReportAction()] = $this->getMerchantAuthentication();

        return $data;
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $data)->send();

        return $this->response = new AIMReportResponse($this, $httpResponse->getBody());
    }


    protected function getMerchantAuthentication()
    {
        $data = array();
        $data['merchantAuthentication'] = array();
        $data['merchantAuthentication']['name'] = $this->getApiLoginId();
        $data['merchantAuthentication']['transactionKey'] = $this->getTransactionKey();

        return $data;

    }
}
