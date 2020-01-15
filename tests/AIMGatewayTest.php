<?php

namespace Omnipay\AuthorizeNet;

use Omnipay\AuthorizeNet\Model\BankAccount;
use Omnipay\Tests\GatewayTestCase;

class AIMGatewayTest extends GatewayTestCase
{
    /** @var AIMGateway */
    protected $gateway;
    protected $purchaseOptions;
    protected $echeckOptions;
    protected $captureOptions;
    protected $voidOptions;
    protected $refundOptions;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new AIMGateway($this->getHttpClient(), $this->getHttpRequest());

        $this->gateway->initialize([
            'hashSecret' => 'HASHYsecretyThang',
        ]);

        $this->purchaseOptions = array(
            'amount' => '10.00',
            'card' => $this->getValidCard(),
            'description' => 'purchase',
        );

        $this->echeckOptions = array(
            'amount' => '10.00',
            'bankAccount' => $this->getValidBankAccount(),
            'description' => 'purchase',
        );

        $this->captureOptions = array(
            'amount' => '10.00',
            'transactionReference' => '12345',
            'description' => 'capture',
        );

        $this->voidOptions = array(
            'transactionReference' => '12345',
            'description' => 'void',
        );

        $this->refundOptions = array(
            'amount' => '10.00',
            'transactionReference' => '12345',
            'card' => $this->getValidCard(),
            'description' => 'refund',
        );
    }

    public function getValidBankAccount()
    {
        return array(
            'firstName' => 'Example',
            'lastName' => 'User',
            'accountType' => 'checking',
            'routingNumber' => '122105155',
            'accountNumber' => '123456789',
            'bankName' => 'U.S. Bank',
            'billingAddress1' => '123 Billing St',
            'billingAddress2' => 'Billsville',
            'billingCity' => 'Billstown',
            'billingPostcode' => '12345',
            'billingState' => 'CA',
            'billingCountry' => 'US',
            'billingPhone' => '(555) 123-4567',
            'shippingAddress1' => '123 Shipping St',
            'shippingAddress2' => 'Shipsville',
            'shippingCity' => 'Shipstown',
            'shippingPostcode' => '54321',
            'shippingState' => 'NY',
            'shippingCountry' => 'US',
            'shippingPhone' => '(555) 987-6543',
        );
    }

    public function testLiveEndpoint()
    {
        $this->assertEquals(
            'https://api2.authorize.net/xml/v1/request.api',
            $this->gateway->getLiveEndpoint()
        );
    }

    public function testDeveloperEndpoint()
    {
        $this->assertEquals(
            'https://apitest.authorize.net/xml/v1/request.api',
            $this->gateway->getDeveloperEndpoint()
        );
    }

    // Added for PR #78
    public function testHashSecret()
    {
        $this->assertEquals(
            'HASHYsecretyThang',
            $this->gateway->getHashSecret()
        );
    }

    private function getExpiry($card)
    {
        return str_pad($card['expiryMonth'] . $card['expiryYear'], 6, '0', STR_PAD_LEFT);
    }

    private function getBankAccountReference($bank) {
        return json_encode(array(
            'accountType' => $bank['accountType'],
            'routingNumber' => $bank['routingNumber'],
            'accountNumber' => $bank['accountNumber'],
            'nameOnAccount' => $bank['firstName'] . ' ' . $bank['lastName'],
            'echeckType' => BankAccount::ECHECK_TYPE_WEB
        ));
    }

    public function testAuthorizeSuccess()
    {
        $this->setMockHttpResponse('AIMAuthorizeSuccess.txt');

        $response = $this->gateway->authorize($this->purchaseOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $expiry = $this->getExpiry($this->purchaseOptions['card']);
        $this->assertSame(
            '{"approvalCode":"GA4OQP","transId":"2184493132","card":{"number":"1111","expiry":"' . $expiry . '"}}',
            $response->getTransactionReference(),
            'should return complex key as transaction reference');
        $this->assertSame('This transaction has been approved.', $response->getMessage());
    }

    public function testAuthorizeFailure()
    {
        $this->setMockHttpResponse('AIMAuthorizeFailure.txt');

        $response = $this->gateway->authorize($this->purchaseOptions)->send();
        $expiry = $this->getExpiry($this->purchaseOptions['card']);

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('{"approvalCode":"","transId":"0","card":{"number":"1111","expiry":"' . $expiry . '"}}', $response->getTransactionReference());
        $this->assertSame('A valid amount is required.', $response->getMessage());
    }

    public function testCaptureSuccess()
    {
        $this->setMockHttpResponse('AIMCaptureSuccess.txt');

        $response = $this->gateway->capture($this->captureOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('{"approvalCode":"F51OYG","transId":"2184494531"}', $response->getTransactionReference());
        $this->assertSame('This transaction has been approved.', $response->getMessage());
    }

    public function testCaptureFailure()
    {
        $this->setMockHttpResponse('AIMCaptureFailure.txt');

        $response = $this->gateway->capture($this->captureOptions)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('{"approvalCode":"","transId":"0"}', $response->getTransactionReference());
        $this->assertSame('The transaction cannot be found.', $response->getMessage());
    }

    public function testCaptureOnlySuccess()
    {
        $this->setMockHttpResponse('AIMCaptureOnlySuccess.txt');

        $response = $this->gateway->capture($this->captureOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('{"approvalCode":"ROHNFQ","transId":"40009379672"}', $response->getTransactionReference());
        $this->assertSame('This transaction has been approved.', $response->getMessage());
    }

    public function testCaptureOnlyFailure()
    {
        $this->setMockHttpResponse('AIMCaptureOnlyFailure.txt');

        $response = $this->gateway->capture($this->captureOptions)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('{"approvalCode":"ROHNFQ","transId":"0"}', $response->getTransactionReference());
        $this->assertSame('A valid amount is required.', $response->getMessage());
    }

    public function testCardPurchaseSuccess()
    {
        $this->setMockHttpResponse('AIMPurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->purchaseOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $expiry = $this->getExpiry($this->purchaseOptions['card']);
        $this->assertSame('{"approvalCode":"JE6JM1","transId":"2184492509","card":{"number":"1111","expiry":"' . $expiry . '"}}', $response->getTransactionReference());
        $this->assertSame('This transaction has been approved.', $response->getMessage());
    }

    public function testCardPurchaseFailure()
    {
        $this->setMockHttpResponse('AIMPurchaseFailure.txt');

        $response = $this->gateway->purchase($this->purchaseOptions)->send();

        $this->assertFalse($response->isSuccessful());
        $expiry = $this->getExpiry($this->purchaseOptions['card']);
        $this->assertSame('{"approvalCode":"","transId":"0","card":{"number":"1111","expiry":"' . $expiry . '"}}', $response->getTransactionReference());
        $this->assertSame('A valid amount is required.', $response->getMessage());
    }

    public function testEcheckPurchaseSuccess()
    {
        $this->setMockHttpResponse('AIMEcheckPurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->echeckOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $bankAccountReference = $this->getBankAccountReference($this->echeckOptions['bankAccount']);
        $this->assertSame('{"approvalCode":"","transId":"2214627492","bankAccount":' . $bankAccountReference . '}', $response->getTransactionReference());
        $this->assertSame('This transaction has been approved.', $response->getMessage());
    }

    public function testEcheckPurchaseFailure()
    {
        $this->setMockHttpResponse('AIMEcheckPurchaseFailure.txt');

        $response = $this->gateway->purchase($this->echeckOptions)->send();

        $this->assertFalse($response->isSuccessful());
        $bankAccountReference = $this->getBankAccountReference($this->echeckOptions['bankAccount']);
        $this->assertSame('{"approvalCode":"","transId":"0","bankAccount":' . $bankAccountReference . '}', $response->getTransactionReference());
        $this->assertSame('The given name on the account and/or the account type does not match the actual account.', $response->getMessage());
    }

    public function testVoidSuccess()
    {
        $this->setMockHttpResponse('AIMVoidSuccess.txt');

        $response = $this->gateway->void($this->voidOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertSame('{"approvalCode":"ZJ5XAB","transId":"2252805912"}', $response->getTransactionReference());
        $this->assertSame('This transaction has been approved.', $response->getMessage());
    }

    public function testVoidFailure()
    {
        $this->setMockHttpResponse('AIMVoidFailure.txt');

        $response = $this->gateway->void($this->voidOptions)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('{"approvalCode":"","transId":"0"}', $response->getTransactionReference());
        $this->assertSame('The transaction cannot be found.', $response->getMessage());
    }

    public function testRefundSuccess()
    {
        $this->setMockHttpResponse('AIMRefundSuccess.txt');

        $response = $this->gateway->refund($this->refundOptions)->send();

        $this->assertTrue($response->isSuccessful());
        $expiry = $this->getExpiry($this->refundOptions['card']);
        $this->assertSame('{"approvalCode":"","transId":"2217770693","card":{"number":"1111","expiry":"' . $expiry . '"}}', $response->getTransactionReference());
        $this->assertSame('This transaction has been approved.', $response->getMessage());
    }

    public function testRefundFailure()
    {
        $this->setMockHttpResponse('AIMRefundFailure.txt');

        $response = $this->gateway->refund($this->refundOptions)->send();

        $this->assertFalse($response->isSuccessful());
        $expiry = $this->getExpiry($this->refundOptions['card']);
        $this->assertSame('{"approvalCode":"","transId":"0","card":{"number":"1111","expiry":"' . $expiry . '"}}', $response->getTransactionReference());
        $this->assertSame('The referenced transaction does not meet the criteria for issuing a credit.', $response->getMessage());
    }
}
