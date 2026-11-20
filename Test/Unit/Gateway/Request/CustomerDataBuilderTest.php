<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Test\Unit\Gateway\Request;

use Avarda\Payments\Gateway\Request\CustomerDataBuilder;
use Magento\Framework\Locale\Resolver;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Model\InfoInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerDataBuilderTest extends TestCase
{
    protected Resolver|MockObject $localeResolverMock;

    protected CustomerDataBuilder $builder;

    public function setUp(): void
    {
        $this->localeResolverMock = $this->createMock(Resolver::class);
        $this->builder = new CustomerDataBuilder($this->localeResolverMock);
    }

    public function testBuildReturnsCustomerData(): void
    {
        $this->localeResolverMock->method('getLocale')->willReturn('fi_FI');

        $billingAddress = $this->createMock(AddressAdapterInterface::class);
        $billingAddress->method('getTelephone')->willReturn('0401234567');
        $billingAddress->method('getEmail')->willReturn('customer@example.com');

        $order = $this->createMock(OrderAdapterInterface::class);
        $order->method('getBillingAddress')->willReturn($billingAddress);

        $payment = $this->createMock(InfoInterface::class);
        $payment->method('getAdditionalInformation')
            ->with('avarda_payments_ssn')
            ->willReturn('030883-925M');

        $paymentDataObject = $this->createMock(PaymentDataObjectInterface::class);
        $paymentDataObject->method('getOrder')->willReturn($order);
        $paymentDataObject->method('getPayment')->willReturn($payment);

        $this->assertSame(
            [
                'customer' => [
                    'identificationNumber' => '030883-925M',
                    'userLanguageCode' => 246,
                    'phone' => '0401234567',
                    'email' => 'customer@example.com',
                ],
            ],
            $this->builder->build(['payment' => $paymentDataObject])
        );
    }

    /**
     * @dataProvider localeProvider
     */
    #[DataProvider('localeProvider')]
    public function testGetUserLanguageCode(string $locale, int $expectedCode): void
    {
        $this->localeResolverMock->method('getLocale')->willReturn($locale);

        $this->assertSame($expectedCode, $this->builder->getUserLanguageCode());
    }

    public static function localeProvider(): array
    {
        return [
            'finnish' => ['fi_FI', 246],
            'swedish' => ['sv_SE', 752],
            'english' => ['en_US', 826],
            'unrecognized falls back to english' => ['de_DE', 826],
        ];
    }
}
