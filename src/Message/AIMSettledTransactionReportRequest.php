<?php

namespace Omnipay\AuthorizeNet\Message;

/**
 * Authorize.Net AIM Report Request
 */
class AIMSettledTransactionReportRequest extends AIMReportRequest
{
    protected $liveEndpoint = 'https://api.authorize.net/xml/v1/request.api';
    protected $developerEndpoint = 'https://apitest.authorize.net/xml/v1/request.api';

    protected $reportAction = 'getSettledBatchListRequest';


    /**
     * @return \DOMDocument
     */
    public function getData()
    {
        $domDocument = parent::getData();
        $firstNode = $domDocument->firstChild;

        $firstSettlementDate = $this->getFirstSettlementDate();
        $lastSettlementDate = $this->getLastSettlementDate();

        if($firstSettlementDate instanceof \DateTime) {
            $firstSettlementDate->setTimezone(new \DateTimeZone('UTC'));
            $firstNode->appendChild($domDocument->createElement('firstSettlementDate', $firstSettlementDate->format('Y-m-d\Th:m:s')));
        } elseif(!is_null($firstSettlementDate)) {
            $firstNode->appendChild($domDocument->createElement('firstSettlementDate', $firstSettlementDate));
        }

        if(!is_null($firstSettlementDate)) {
            if ($lastSettlementDate instanceof \DateTime) {
                $lastSettlementDate->setTimezone(new \DateTimeZone('UTC'));
                $firstNode->appendChild($domDocument->createElement('lastSettlementDate', $lastSettlementDate->format('Y-m-d\Th:m:s')));
            } elseif (!is_null($lastSettlementDate)) {
                $firstNode->appendChild($domDocument->createElement('lastSettlementDate', $lastSettlementDate));
            }
        }

        return $domDocument;
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post($this->getEndpoint(), array('Content-Type' => 'text/xml'), $data->saveXML())->send();
        return $this->response = new AIMSettledTransactionReportResponse($this, $httpResponse->getBody());
    }

    public function getFirstSettlementDate()
    {
        return $this->getParameter('firstSettlementDate');
    }

    public function setFirstSettlementDate($value)
    {
        return $this->setParameter('firstSettlementDate', $value);
    }

    public function getLastSettlementDate()
    {
        return $this->getParameter('lastSettlementDate');
    }

    public function setLastSettlementDate($value)
    {
        return $this->setParameter('lastSettlementDate', $value);
    }

    public function getIncludeStatistics()
    {
        return $this->getParameter('includeStatistics');
    }

    public function setIncludeStatistics($value)
    {
        return $this->setParameter('includeStatistics', $value);
    }

}
