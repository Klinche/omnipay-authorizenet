<?php

namespace Omnipay\AuthorizeNet\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Authorize.Net AIM Report Response
 */
class AIMReportResponse extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
    }

    public function isSuccessful()
    {
        return '1' === $this->getCode();
    }
}
