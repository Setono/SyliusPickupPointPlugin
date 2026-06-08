<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\Address;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class AddressTest extends TestCase
{
    public function testItDefaultsAllFieldsToNull(): void
    {
        $address = new Address();

        self::assertNull($address->street);
        self::assertNull($address->postalCode);
        self::assertNull($address->city);
        self::assertNull($address->countryCode);
    }

    public function testItMapsTheShippingAddressFieldsFromTheOrder(): void
    {
        $shippingAddress = $this->createMock(AddressInterface::class);
        $shippingAddress->method('getStreet')->willReturn('Some street 1');
        $shippingAddress->method('getPostcode')->willReturn('9000');
        $shippingAddress->method('getCity')->willReturn('Aalborg');
        $shippingAddress->method('getCountryCode')->willReturn('DK');

        $order = $this->createMock(OrderInterface::class);
        $order->method('getShippingAddress')->willReturn($shippingAddress);

        $address = Address::fromOrder($order);

        self::assertSame('Some street 1', $address->street);
        self::assertSame('9000', $address->postalCode);
        self::assertSame('Aalborg', $address->city);
        self::assertSame('DK', $address->countryCode);
    }

    public function testItReturnsAnAllNullAddressWhenTheOrderHasNoShippingAddress(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getShippingAddress')->willReturn(null);

        $address = Address::fromOrder($order);

        self::assertNull($address->street);
        self::assertNull($address->postalCode);
        self::assertNull($address->city);
        self::assertNull($address->countryCode);
    }
}
