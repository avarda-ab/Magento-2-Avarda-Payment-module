<?php
/**
 * @copyright Copyright © Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Test\Unit\Observer;

use Avarda\Payments\Observer\SsnDataAssigner;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SsnDataAssignerTest extends TestCase
{
    protected InfoInterface|MockObject $paymentMock;

    protected SsnDataAssigner $assigner;

    public function setUp(): void
    {
        $this->paymentMock = $this->createMock(InfoInterface::class);
        $this->assigner = new SsnDataAssigner();
    }

    public function createObserver(mixed $additionalData): Observer
    {
        $event = new Event([
            AbstractDataAssignObserver::DATA_CODE => new DataObject([
                PaymentInterface::KEY_ADDITIONAL_DATA => $additionalData,
            ]),
            AbstractDataAssignObserver::MODEL_CODE => $this->paymentMock,
        ]);

        return new Observer(['event' => $event]);
    }

    public function testSsnIsCopiedToPaymentAdditionalInformation(): void
    {
        $this->paymentMock
            ->expects($this->once())
            ->method('setAdditionalInformation')
            ->with('avarda_payments_ssn', '030883-925M');

        $this->assigner->execute($this->createObserver(['avarda_payments_ssn' => '030883-925M']));
    }

    public function testNothingIsSetWithoutSsn(): void
    {
        $this->paymentMock->expects($this->never())->method('setAdditionalInformation');

        $this->assigner->execute($this->createObserver(['other' => 'value']));
    }

    public function testNothingIsSetWhenAdditionalDataIsNotArray(): void
    {
        $this->paymentMock->expects($this->never())->method('setAdditionalInformation');

        $this->assigner->execute($this->createObserver(null));
    }
}
