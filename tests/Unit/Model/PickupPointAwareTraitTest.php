<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareTrait;

final class PickupPointAwareTraitTest extends TestCase
{
    /**
     * Returns the trait under test exposed as a named helper class. The helper additionally
     * exposes the protected `pickup_point` Doctrine column so the test can assert the exact
     * value that would be stored (and that gets decoded back into a {@see PickupPoint}).
     */
    private function getSubject(): PickupPointAwareTestSubject
    {
        return new PickupPointAwareTestSubject();
    }

    public function testItDefaultsToNoPickupPoint(): void
    {
        $subject = $this->getSubject();

        self::assertNull($subject->getPickupPoint());
        self::assertFalse($subject->hasPickupPoint());
    }

    public function testItSetsAndGetsThePickupPoint(): void
    {
        $subject = $this->getSubject();
        $pickupPoint = PickupPoint::fromArray([
            'provider' => 'gls',
            'id' => '12345',
            'name' => 'Post office',
            'country' => 'DK',
            'metadata' => ['region' => 'north'],
        ]);

        $subject->setPickupPoint($pickupPoint);

        self::assertTrue($subject->hasPickupPoint());
        self::assertSame($pickupPoint, $subject->getPickupPoint());
    }

    public function testItStoresThePickupPointInTheJsonColumnShape(): void
    {
        $subject = $this->getSubject();
        $pickupPoint = PickupPoint::fromArray([
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
        ]);

        $subject->setPickupPoint($pickupPoint);

        $stored = $subject->getStoredColumn();
        self::assertInstanceOf(PickupPoint::class, $stored);

        // The Doctrine JSON column serializes the DTO via jsonSerialize(); assert that exact array shape.
        self::assertSame([
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
        ], $stored->jsonSerialize());
    }

    public function testItDecodesThePickupPointWhenStoredAsAnArray(): void
    {
        $subject = $this->getSubject();
        $stored = [
            'provider' => 'gls',
            'id' => '12345',
            'name' => 'Post office',
            'country' => 'DK',
            'metadata' => ['region' => 'north', 'warehouse' => 7],
        ];

        // Simulate Doctrine loading the row: the JSON column comes back as a plain array.
        $subject->setStoredColumn($stored);

        self::assertTrue($subject->hasPickupPoint());

        $pickupPoint = $subject->getPickupPoint();

        self::assertInstanceOf(PickupPoint::class, $pickupPoint);
        self::assertSame('gls', $pickupPoint->provider);
        self::assertSame('12345', $pickupPoint->id);
        self::assertSame('DK', $pickupPoint->country);
        self::assertSame(['region' => 'north', 'warehouse' => 7], $pickupPoint->metadata);
    }

    public function testItRoundTripsThroughTheJsonColumnLikeDoctrine(): void
    {
        $writer = $this->getSubject();
        $pickupPoint = PickupPoint::fromArray([
            'provider' => 'postnord',
            'id' => '67890',
            'name' => 'Kiosk',
            'country' => 'SE',
            'metadata' => ['token' => 'abc'],
        ]);

        $writer->setPickupPoint($pickupPoint);

        // Persist + reload: encode the stored value to JSON, decode it, hand it to a fresh entity as the column value.
        $encoded = json_encode($writer->getStoredColumn(), \JSON_THROW_ON_ERROR);
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($encoded, true, 512, \JSON_THROW_ON_ERROR);

        $reader = $this->getSubject();
        $reader->setStoredColumn($decoded);

        self::assertEquals($pickupPoint, $reader->getPickupPoint());
    }

    public function testItResetsToNoPickupPoint(): void
    {
        $subject = $this->getSubject();
        $subject->setPickupPoint(PickupPoint::fromArray(['provider' => 'gls', 'id' => '1']));

        $subject->setPickupPoint(null);

        self::assertNull($subject->getPickupPoint());
        self::assertFalse($subject->hasPickupPoint());
    }
}

final class PickupPointAwareTestSubject implements PickupPointAwareInterface
{
    use PickupPointAwareTrait;

    /**
     * @return array<string, mixed>|PickupPoint|null
     */
    public function getStoredColumn(): null|PickupPoint|array
    {
        return $this->pickupPoint;
    }

    /**
     * Mimics Doctrine rehydrating the JSON column: the stored value becomes a plain array.
     *
     * @param array<string, mixed> $value
     */
    public function setStoredColumn(array $value): void
    {
        $this->pickupPoint = $value;
    }
}
