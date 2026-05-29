<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\DTO;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;

final class PickupPointIdentifierTest extends TestCase
{
    public function testItBuildsFromACompletePickupPointFoldingCountryIntoMetadata(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'gls';
        $pickupPoint->id = '12345';
        $pickupPoint->country = 'DK';
        $pickupPoint->metadata = ['region' => 'north'];

        $identifier = PickupPointIdentifier::fromPickupPoint($pickupPoint);

        self::assertNotNull($identifier);
        self::assertSame('gls', $identifier->provider);
        self::assertSame('12345', $identifier->id);
        // the first-class country property travels inside the identifier's metadata
        self::assertSame(['country' => 'DK', 'region' => 'north'], $identifier->metadata);
    }

    public function testItReturnsNullWhenProviderIsMissing(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->id = '12345';

        self::assertNull(PickupPointIdentifier::fromPickupPoint($pickupPoint));
    }

    public function testItReturnsNullWhenIdIsMissing(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'gls';

        self::assertNull(PickupPointIdentifier::fromPickupPoint($pickupPoint));
    }

    public function testItBuildsFromAnArray(): void
    {
        $identifier = PickupPointIdentifier::fromArray([
            'provider' => 'gls',
            'id' => '12345',
            'metadata' => ['country' => 'DK'],
        ]);

        self::assertSame('gls', $identifier->provider);
        self::assertSame('12345', $identifier->id);
        self::assertSame(['country' => 'DK'], $identifier->metadata);
    }

    public function testFromArrayDefaultsMetadataToAnEmptyArray(): void
    {
        $identifier = PickupPointIdentifier::fromArray(['provider' => 'gls', 'id' => '12345']);

        self::assertSame([], $identifier->metadata);
    }

    public function testFromArrayThrowsWhenProviderIsMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PickupPointIdentifier::fromArray(['id' => '12345']);
    }

    public function testFromArrayThrowsWhenIdIsMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PickupPointIdentifier::fromArray(['provider' => 'gls']);
    }

    public function testItSerializesToTheWireShape(): void
    {
        $identifier = new PickupPointIdentifier('gls', '12345', ['country' => 'DK']);

        self::assertSame(
            ['provider' => 'gls', 'id' => '12345', 'metadata' => ['country' => 'DK']],
            $identifier->jsonSerialize(),
        );
    }

    public function testFromArrayIsTheInverseOfJsonSerialize(): void
    {
        $identifier = new PickupPointIdentifier('gls', '12345', ['country' => 'DK', 'region' => 'north']);

        self::assertEquals($identifier, PickupPointIdentifier::fromArray($identifier->jsonSerialize()));
    }
}
