<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;

final class PickupPointTest extends TestCase
{
    public function testItRoundTripsThroughArrayKeepingCountryAndMetadata(): void
    {
        $data = [
            'provider' => 'gls',
            'id' => '12345',
            'name' => 'Post office',
            'address' => 'Some street 1',
            'zipCode' => '9000',
            'city' => 'Aalborg',
            'country' => 'DK',
            'latitude' => '57.0',
            'longitude' => '9.9',
            'metadata' => ['region' => 'north', 'warehouse' => 7],
        ];

        $pickupPoint = PickupPoint::fromArray($data);

        self::assertSame('DK', $pickupPoint->country);
        self::assertSame(['region' => 'north', 'warehouse' => 7], $pickupPoint->metadata);
        self::assertSame($data, $pickupPoint->jsonSerialize());
    }

    public function testItDefaultsCountryAndMetadataWhenAbsent(): void
    {
        $pickupPoint = PickupPoint::fromArray(['provider' => 'gls', 'id' => '1']);

        self::assertNull($pickupPoint->country);
        self::assertSame([], $pickupPoint->metadata);
    }

    public function testItDefaultsMetadataToAnEmptyArrayWhenItIsNotAnArray(): void
    {
        $pickupPoint = PickupPoint::fromArray(['provider' => 'gls', 'id' => '1', 'metadata' => 'not-an-array']);

        self::assertSame([], $pickupPoint->metadata);
    }
}
