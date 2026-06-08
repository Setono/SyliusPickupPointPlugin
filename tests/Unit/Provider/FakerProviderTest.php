<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Provider;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Provider\FakerProvider;

final class FakerProviderTest extends TestCase
{
    public function testItGeneratesTenPickupPointsStampedWithTheProviderCodeAndCountry(): void
    {
        $provider = new FakerProvider();
        $provider->setCode('faker');

        $pickupPoints = $provider->findPickupPoints(new Address(countryCode: 'DK'));

        self::assertCount(10, $pickupPoints);

        foreach ($pickupPoints as $pickupPoint) {
            self::assertInstanceOf(PickupPoint::class, $pickupPoint);
            self::assertSame('faker', $pickupPoint->provider);
            self::assertSame('DK', $pickupPoint->country);
        }
    }

    public function testItFindsASinglePickupPointByIdUsingTheCountryFromMetadata(): void
    {
        $provider = new FakerProvider();
        $provider->setCode('faker');

        $pickupPoint = $provider->findPickupPoint('3', ['country' => 'DK']);

        self::assertSame('3', $pickupPoint->id);
        self::assertSame('faker', $pickupPoint->provider);
        self::assertSame('DK', $pickupPoint->country);
    }
}
