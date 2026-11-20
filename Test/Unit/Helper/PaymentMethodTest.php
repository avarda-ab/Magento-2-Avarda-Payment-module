<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Test\Unit\Helper;

use Avarda\Payments\Helper\PaymentMethod;
use Avarda\Payments\Model\Ui\AlternativeDirectInvoice\ConfigProvider as AlternativeDirectInvoiceConfigProvider;
use Avarda\Payments\Model\Ui\DirectInvoice\ConfigProvider as DirectInvoiceConfigProvider;
use Avarda\Payments\Model\Ui\Invoice\ConfigProvider as InvoiceConfigProvider;
use Avarda\Payments\Model\Ui\Loan\ConfigProvider as LoanConfigProvider;
use Avarda\Payments\Model\Ui\PartPayment\ConfigProvider as PartPaymentConfigProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PaymentMethodTest extends TestCase
{
    /**
     * @dataProvider methodCodeProvider
     */
    #[DataProvider('methodCodeProvider')]
    public function testGetPaymentMethod(string $methodCode, string $expected): void
    {
        $this->assertSame($expected, (new PaymentMethod())->getPaymentMethod($methodCode));
    }

    public static function methodCodeProvider(): array
    {
        return [
            'alternative direct invoice' => [AlternativeDirectInvoiceConfigProvider::CODE, 'DirectInvoice'],
            'direct invoice' => [DirectInvoiceConfigProvider::CODE, 'DirectInvoice'],
            'invoice' => [InvoiceConfigProvider::CODE, 'Invoice'],
            'loan' => [LoanConfigProvider::CODE, 'Loan'],
            'part payment' => [PartPaymentConfigProvider::CODE, 'PartPayment'],
        ];
    }
}
