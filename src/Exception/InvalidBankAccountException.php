<?php

namespace Omnipay\AuthorizeNet\Exception;

use Omnipay\Common\Exception\OmnipayException;

/**
 * Invalid Credit Card Exception
 *
 * Thrown when a credit card is invalid or missing required fields.
 */
class InvalidBankAccountException extends \Exception implements OmnipayException
{
}
