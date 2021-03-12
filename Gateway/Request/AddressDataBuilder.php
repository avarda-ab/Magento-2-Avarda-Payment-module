<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Payments
 */
namespace Avarda\Payments\Gateway\Request;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * The first name value must be less than or equal to 40 characters.
     */
    const FIRST_NAME = 'firstName';

    /**
     * The last name value must be less than or equal to 40 characters.
     */
    const LAST_NAME = 'lastName';

    /**
     * The street address line 1. Maximum 40 characters.
     */
    const STREET_1 = 'addressLine1';

    /**
     * The street address line 2. Maximum 40 characters.
     */
    const STREET_2 = 'addressLine2';

    /**
     * The Zip/Postal code. Maximum 6 characters.
     */
    const ZIP = 'zip';

    /**
     * The locality/city. 30 character maximum.
     */
    const CITY = 'city';

    /**
     * country
     */
    const COUNTRY = 'country';

    /**
     * If delivery adress is diffrent the payment
    */
    const USE_DIFFERENT_DELIVERY_ADDRESS = 'UseDifferentDeliveryAddress';

    /**
     * Customers email address
     */
    const EMAIL = "Mail";

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        return [
            "invoicingAddress" => $this->setBillingAddress($order),
            "deliveryAddress" => $this->setShippingAddress($order)
        ];
    }

    /**
     * @param OrderAdapterInterface $order
     * @return array
     */
    protected function setBillingAddress(OrderAdapterInterface $order)
    {
        $address = $order->getBillingAddress();
        if ($address === null) {
            return [];
        }

        return [
            self::FIRST_NAME => $address->getFirstname(),
            self::LAST_NAME  => $address->getLastname(),
            self::STREET_1   => $address->getStreetLine1(),
            self::STREET_2   => $address->getStreetLine2(),
            self::ZIP        => $address->getPostcode(),
            self::CITY       => $address->getCity(),
            self::COUNTRY    => $address->getCountryId(),
        ];
    }

    /**
     * @param OrderAdapterInterface $order
     * @return array
     */
    protected function setShippingAddress(OrderAdapterInterface $order)
    {
        $address = $order->getShippingAddress();
        if ($address === null) {
            // If it's virtual order it doesn't have shipping address
            return $this->setBillingAddress($order);
        }

        return [
            self::FIRST_NAME => $address->getFirstname(),
            self::LAST_NAME  => $address->getLastname(),
            self::STREET_1   => $address->getStreetLine1(),
            self::STREET_2   => $address->getStreetLine2(),
            self::ZIP        => $address->getPostcode(),
            self::CITY       => $address->getCity(),
            self::COUNTRY    => $address->getCountryId(),
        ];
    }
}
