<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Test\Unit\Helper;

use Avarda\Payments\Helper\PaymentData;
use Magento\Quote\Model\Quote\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentDataTest extends TestCase
{
    protected PaymentData $paymentData;

    public function setUp(): void
    {
        $this->paymentData = new PaymentData();
    }

    public function createPayment(mixed $additionalInformation, string $method = ''): Payment|MockObject
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getAdditionalInformation')->willReturn($additionalInformation);
        $payment->method('getMethod')->willReturn($method);

        return $payment;
    }

    public function testGetAuthorizeIdReturnsStoredId(): void
    {
        $payment = $this->createPayment(['authorization_id' => 'abc123']);

        $this->assertSame('abc123', $this->paymentData->getAuthorizeId($payment));
    }

    public function testGetAuthorizeIdReturnsFalseWithoutId(): void
    {
        $this->assertFalse($this->paymentData->getAuthorizeId($this->createPayment([])));
        $this->assertFalse($this->paymentData->getAuthorizeId($this->createPayment(null)));
    }

    /**
     * @dataProvider avardaPaymentProvider
     */
    #[DataProvider('avardaPaymentProvider')]
    public function testIsAvardaPayment(array $additionalInformation, string $method, bool $expected): void
    {
        $payment = $this->createPayment($additionalInformation, $method);

        $this->assertSame($expected, $this->paymentData->isAvardaPayment($payment));
    }

    public static function avardaPaymentProvider(): array
    {
        return [
            'avarda method with authorization' => [
                ['authorization_id' => 'abc123'],
                'avarda_payments_direct_invoice',
                true,
            ],
            'avarda method without authorization' => [[], 'avarda_payments_direct_invoice', false],
            'other method with authorization' => [['authorization_id' => 'abc123'], 'checkmo', false],
            'no method set with authorization' => [['authorization_id' => 'abc123'], '', true],
        ];
    }
}
