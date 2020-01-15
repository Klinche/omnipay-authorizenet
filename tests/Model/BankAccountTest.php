<?php

namespace Omnipay\AuthorizeNet\Model;

use Omnipay\Tests\TestCase;

class BankAccountTest extends TestCase
{
    /** @var BankAccount */
    private $bank;

    public function setUp()
    {
        $this->bank = new BankAccount;
        $this->bank->setAccountType('checking');
        $this->bank->setRoutingNumber('122105155');
        $this->bank->setAccountNumber('123456789');
        $this->bank->setFirstName('Example');
        $this->bank->setLastName('Customer');
    }

    public function testConstructWithParams()
    {
        $bank = new BankAccount(array('name' => 'Test Customer'));
        $this->assertSame('Test Customer', $bank->getName());
    }

    public function testInitializeWithParams()
    {
        $bank = new BankAccount;
        $bank->initialize(array('name' => 'Test Customer'));
        $this->assertSame('Test Customer', $bank->getName());
    }

    public function testGetParamters()
    {
        $bank = new BankAccount(array(
            'name' => 'Example Customer',
            'routingNumber' => '123456789',
            'accountNumber' => '987654321',
            'accountType' => 'checking'
        ));

        $parameters = $bank->getParameters();
        $this->assertSame('Example', $parameters['billingFirstName']);
        $this->assertSame('Customer', $parameters['billingLastName']);
        $this->assertSame('123456789', $parameters['routingNumber']);
        $this->assertSame('987654321', $parameters['accountNumber']);
        $this->assertSame('checking', $parameters['accountType']);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidateFixture()
    {
        $this->bank->validate();
    }

    /**
     * @expectedException \Omnipay\AuthorizeNet\Exception\InvalidBankAccountException
     * @expectedExceptionMessage The bank account type is required
     */
    public function testValidateAccountTypeRequired()
    {
        $this->bank->setAccountType(null);
        $this->bank->validate();
    }

    /**
     * @expectedException \Omnipay\AuthorizeNet\Exception\InvalidBankAccountException
     * @expectedExceptionMessage The bank routing number is required
     */
    public function testValidateRoutingNumberRequired()
    {
        $this->bank->setRoutingNumber(null);
        $this->bank->validate();
    }

    /**
     * @expectedException \Omnipay\AuthorizeNet\Exception\InvalidBankAccountException
     * @expectedExceptionMessage The bank account number is required
     */
    public function testValidateAccountNumberRequired()
    {
        $this->bank->setAccountNumber(null);
        $this->bank->validate();
    }

    /**
     * @expectedException \Omnipay\AuthorizeNet\Exception\InvalidBankAccountException
     * @expectedExceptionMessage The bank account type is not in the supported list
     */
    public function testValidateAccountType()
    {
        $this->bank->setAccountType(BankAccount::ACCOUNT_TYPE_BUSINESS_CHECKING);
        $this->bank->validate();
    }

    /**
     * @expectedException \Omnipay\AuthorizeNet\Exception\InvalidBankAccountException
     * @expectedExceptionMessage The bank routing number should have 9 digits
     */
    public function testValidateRoutingNumberLength()
    {
        $this->bank->setRoutingNumber(12345678);
        $this->bank->validate();
    }

    /**
     * @expectedException \Omnipay\AuthorizeNet\Exception\InvalidBankAccountException
     * @expectedExceptionMessage The bank routing number is invalid
     */
    public function testValidateRoutingNumber()
    {
        $this->bank->setRoutingNumber(123456789);
        $this->bank->validate();
    }

    public function testGetSupportedAccountTypes()
    {
        $accountTypes = $this->bank->getSupportedAccountTypes();
        $this->assertInternalType('array', $accountTypes);
        $this->assertContains(BankAccount::ACCOUNT_TYPE_CHECKING, $accountTypes);
    }

    public function testTitle()
    {
        $this->bank->setTitle('Mr.');
        $this->assertEquals('Mr.', $this->bank->getTitle());
    }

    public function testFirstName()
    {
        $this->bank->setFirstName('Bob');
        $this->assertEquals('Bob', $this->bank->getFirstName());
    }

    public function testLastName()
    {
        $this->bank->setLastName('Smith');
        $this->assertEquals('Smith', $this->bank->getLastName());
    }

    public function testGetName()
    {
        $this->bank->setFirstName('Bob');
        $this->bank->setLastName('Smith');
        $this->assertEquals('Bob Smith', $this->bank->getName());
    }

    public function testSetName()
    {
        $this->bank->setName('Bob Smith');
        $this->assertEquals('Bob', $this->bank->getFirstName());
        $this->assertEquals('Smith', $this->bank->getLastName());
    }

    public function testSetNameWithOneName()
    {
        $this->bank->setName('Bob');
        $this->assertEquals('Bob', $this->bank->getFirstName());
        $this->assertEquals('', $this->bank->getLastName());
    }

    public function testSetNameWithMultipleNames()
    {
        $this->bank->setName('Bob John Smith');
        $this->assertEquals('Bob', $this->bank->getFirstName());
        $this->assertEquals('John Smith', $this->bank->getLastName());
    }

    public function testRoutingNumber()
    {
        $this->bank->setRoutingNumber('123456789');
        $this->assertEquals('123456789', $this->bank->getRoutingNumber());
    }

    public function testSetRoutingNumberStripsNonDigits()
    {
        $this->bank->setRoutingNumber('12345 6789');
        $this->assertEquals('123456789', $this->bank->getRoutingNumber());
    }

    public function testAccountNumber()
    {
        $this->bank->setAccountNumber('123456789');
        $this->assertEquals('123456789', $this->bank->getAccountNumber());
    }

    public function testSetAccountNumberStripsNonDigits()
    {
        $this->bank->setAccountNumber('12345 6789');
        $this->assertEquals('123456789', $this->bank->getAccountNumber());
    }

    public function testGetAccountNumberLastFourNull()
    {
        $this->bank->setAccountNumber(null);
        $this->assertNull($this->bank->getAccountNumberLastFour());
    }

    public function testGetAccountNumberLastFour()
    {
        $this->bank->setAccountNumber('111116789');
        $this->assertSame('6789', $this->bank->getAccountNumberLastFour());
    }

    public function testGetAccountNumberLastFourNonDigits()
    {
        $this->bank->setAccountNumber('11111 67x89');
        $this->assertSame('6789', $this->bank->getAccountNumberLastFour());
    }

    public function testGetAccountNumberMasked()
    {
        $this->bank->setAccountNumber('111116789');
        $this->assertSame('XXXXX6789', $this->bank->getAccountNumberMasked());
    }

    public function testGetAccountNumberMaskedNonDigits()
    {
        $this->bank->setAccountNumber('11111 67x89');
        $this->assertSame('XXXXX6789', $this->bank->getAccountNumberMasked());
    }

    public function testBillingTitle()
    {
        $this->bank->setBillingTitle('Mrs.');
        $this->assertEquals('Mrs.', $this->bank->getBillingTitle());
        $this->assertEquals('Mrs.', $this->bank->getTitle());
    }

    public function testBillingFirstName()
    {
        $this->bank->setBillingFirstName('Bob');
        $this->assertEquals('Bob', $this->bank->getBillingFirstName());
        $this->assertEquals('Bob', $this->bank->getFirstName());
    }

    public function testBillingLastName()
    {
        $this->bank->setBillingLastName('Smith');
        $this->assertEquals('Smith', $this->bank->getBillingLastName());
        $this->assertEquals('Smith', $this->bank->getLastName());
    }

    public function testBillingName()
    {
        $this->bank->setBillingFirstName('Bob');
        $this->bank->setBillingLastName('Smith');
        $this->assertEquals('Bob Smith', $this->bank->getBillingName());

        $this->bank->setBillingName('John Foo');
        $this->assertEquals('John', $this->bank->getBillingFirstName());
        $this->assertEquals('Foo', $this->bank->getBillingLastName());
    }

    public function testBillingCompany()
    {
        $this->bank->setBillingCompany('SuperSoft');
        $this->assertEquals('SuperSoft', $this->bank->getBillingCompany());
        $this->assertEquals('SuperSoft', $this->bank->getCompany());
    }

    public function testBillingAddress1()
    {
        $this->bank->setBillingAddress1('31 Spooner St');
        $this->assertEquals('31 Spooner St', $this->bank->getBillingAddress1());
        $this->assertEquals('31 Spooner St', $this->bank->getAddress1());
    }

    public function testBillingAddress2()
    {
        $this->bank->setBillingAddress2('Suburb');
        $this->assertEquals('Suburb', $this->bank->getBillingAddress2());
        $this->assertEquals('Suburb', $this->bank->getAddress2());
    }

    public function testBillingCity()
    {
        $this->bank->setBillingCity('Quahog');
        $this->assertEquals('Quahog', $this->bank->getBillingCity());
        $this->assertEquals('Quahog', $this->bank->getCity());
    }

    public function testBillingPostcode()
    {
        $this->bank->setBillingPostcode('12345');
        $this->assertEquals('12345', $this->bank->getBillingPostcode());
        $this->assertEquals('12345', $this->bank->getPostcode());
    }

    public function testBillingState()
    {
        $this->bank->setBillingState('RI');
        $this->assertEquals('RI', $this->bank->getBillingState());
        $this->assertEquals('RI', $this->bank->getState());
    }

    public function testBillingCountry()
    {
        $this->bank->setBillingCountry('US');
        $this->assertEquals('US', $this->bank->getBillingCountry());
        $this->assertEquals('US', $this->bank->getCountry());
    }

    public function testBillingPhone()
    {
        $this->bank->setBillingPhone('12345');
        $this->assertSame('12345', $this->bank->getBillingPhone());
        $this->assertSame('12345', $this->bank->getPhone());
    }

    public function testBillingPhoneExtension()
    {
        $this->bank->setBillingPhoneExtension('001');
        $this->assertSame('001', $this->bank->getBillingPhoneExtension());
        $this->assertSame('001', $this->bank->getPhoneExtension());
    }

    public function testBillingFax()
    {
        $this->bank->setBillingFax('54321');
        $this->assertSame('54321', $this->bank->getBillingFax());
        $this->assertSame('54321', $this->bank->getFax());
    }

    public function testShippingTitle()
    {
        $this->bank->setShippingTitle('Dr.');
        $this->assertEquals('Dr.', $this->bank->getShippingTitle());
    }

    public function testShippingFirstName()
    {
        $this->bank->setShippingFirstName('James');
        $this->assertEquals('James', $this->bank->getShippingFirstName());
    }

    public function testShippingLastName()
    {
        $this->bank->setShippingLastName('Doctor');
        $this->assertEquals('Doctor', $this->bank->getShippingLastName());
    }

    public function testShippingName()
    {
        $this->bank->setShippingFirstName('Bob');
        $this->bank->setShippingLastName('Smith');
        $this->assertEquals('Bob Smith', $this->bank->getShippingName());

        $this->bank->setShippingName('John Foo');
        $this->assertEquals('John', $this->bank->getShippingFirstName());
        $this->assertEquals('Foo', $this->bank->getShippingLastName());
    }

    public function testShippingCompany()
    {
        $this->bank->setShippingCompany('SuperSoft');
        $this->assertEquals('SuperSoft', $this->bank->getShippingCompany());
    }

    public function testShippingAddress1()
    {
        $this->bank->setShippingAddress1('31 Spooner St');
        $this->assertEquals('31 Spooner St', $this->bank->getShippingAddress1());
    }

    public function testShippingAddress2()
    {
        $this->bank->setShippingAddress2('Suburb');
        $this->assertEquals('Suburb', $this->bank->getShippingAddress2());
    }

    public function testShippingCity()
    {
        $this->bank->setShippingCity('Quahog');
        $this->assertEquals('Quahog', $this->bank->getShippingCity());
    }

    public function testShippingPostcode()
    {
        $this->bank->setShippingPostcode('12345');
        $this->assertEquals('12345', $this->bank->getShippingPostcode());
    }

    public function testShippingState()
    {
        $this->bank->setShippingState('RI');
        $this->assertEquals('RI', $this->bank->getShippingState());
    }

    public function testShippingCountry()
    {
        $this->bank->setShippingCountry('US');
        $this->assertEquals('US', $this->bank->getShippingCountry());
    }

    public function testShippingPhone()
    {
        $this->bank->setShippingPhone('12345');
        $this->assertEquals('12345', $this->bank->getShippingPhone());
    }

    public function testShippingPhoneExtension()
    {
        $this->bank->setShippingPhoneExtension('001');
        $this->assertEquals('001', $this->bank->getShippingPhoneExtension());
    }

    public function testShippingFax()
    {
        $this->bank->setShippingFax('54321');
        $this->assertEquals('54321', $this->bank->getShippingFax());
    }

    public function testCompany()
    {
        $this->bank->setCompany('FooBar');
        $this->assertEquals('FooBar', $this->bank->getCompany());
        $this->assertEquals('FooBar', $this->bank->getBillingCompany());
        $this->assertEquals('FooBar', $this->bank->getShippingCompany());
    }

    public function testAddress1()
    {
        $this->bank->setAddress1('31 Spooner St');
        $this->assertEquals('31 Spooner St', $this->bank->getAddress1());
        $this->assertEquals('31 Spooner St', $this->bank->getBillingAddress1());
        $this->assertEquals('31 Spooner St', $this->bank->getShippingAddress1());
    }

    public function testAddress2()
    {
        $this->bank->setAddress2('Suburb');
        $this->assertEquals('Suburb', $this->bank->getAddress2());
        $this->assertEquals('Suburb', $this->bank->getBillingAddress2());
        $this->assertEquals('Suburb', $this->bank->getShippingAddress2());
    }

    public function testCity()
    {
        $this->bank->setCity('Quahog');
        $this->assertEquals('Quahog', $this->bank->getCity());
        $this->assertEquals('Quahog', $this->bank->getBillingCity());
        $this->assertEquals('Quahog', $this->bank->getShippingCity());
    }

    public function testPostcode()
    {
        $this->bank->setPostcode('12345');
        $this->assertEquals('12345', $this->bank->getPostcode());
        $this->assertEquals('12345', $this->bank->getBillingPostcode());
        $this->assertEquals('12345', $this->bank->getShippingPostcode());
    }

    public function testState()
    {
        $this->bank->setState('RI');
        $this->assertEquals('RI', $this->bank->getState());
        $this->assertEquals('RI', $this->bank->getBillingState());
        $this->assertEquals('RI', $this->bank->getShippingState());
    }

    public function testCountry()
    {
        $this->bank->setCountry('US');
        $this->assertEquals('US', $this->bank->getCountry());
        $this->assertEquals('US', $this->bank->getBillingCountry());
        $this->assertEquals('US', $this->bank->getShippingCountry());
    }

    public function testPhone()
    {
        $this->bank->setPhone('12345');
        $this->assertEquals('12345', $this->bank->getPhone());
        $this->assertEquals('12345', $this->bank->getBillingPhone());
        $this->assertEquals('12345', $this->bank->getShippingPhone());
    }

    public function testPhoneExtension()
    {
        $this->bank->setPhoneExtension('001');
        $this->assertEquals('001', $this->bank->getPhoneExtension());
        $this->assertEquals('001', $this->bank->getBillingPhoneExtension());
        $this->assertEquals('001', $this->bank->getShippingPhoneExtension());
    }

    public function testFax()
    {
        $this->bank->setFax('54321');
        $this->assertEquals('54321', $this->bank->getFax());
        $this->assertEquals('54321', $this->bank->getBillingFax());
        $this->assertEquals('54321', $this->bank->getShippingFax());
    }

    public function testEmail()
    {
        $this->bank->setEmail('adrian@example.com');
        $this->assertEquals('adrian@example.com', $this->bank->getEmail());
    }

    public function testBirthday()
    {
        $this->bank->setBirthday('01-02-2000');
        $this->assertEquals('2000-02-01', $this->bank->getBirthday());
        $this->assertEquals('01/02/2000', $this->bank->getBirthday('d/m/Y'));
    }

    public function testBirthdayEmpty()
    {
        $this->bank->setBirthday('');
        $this->assertNull($this->bank->getBirthday());
    }

    public function testGender()
    {
        $this->bank->setGender('female');
        $this->assertEquals('female', $this->bank->getGender());
    }
}
