<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Serializer\Normalizer;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;
use Setono\SyliusPickupPointPlugin\Serializer\Normalizer\PickupPointNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Serializer;

final class PickupPointNormalizerTest extends TestCase
{
    private PickupPointIdentifierEncoder $encoder;

    private PickupPointNormalizer $normalizer;

    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->encoder = new PickupPointIdentifierEncoder();
        $this->normalizer = new PickupPointNormalizer($this->encoder);

        // Mirrors the framework wiring: building the Serializer injects itself into the
        // NormalizerAware normalizers, so our normalizer can delegate the base representation
        // (which, for a JsonSerializable PickupPoint, lands on JsonSerializableNormalizer).
        $this->serializer = new Serializer([$this->normalizer, new JsonSerializableNormalizer()], []);
    }

    public function testItAddsTheEncodedIdentifierToTheDelegatedShape(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'faker';
        $pickupPoint->id = '0';
        $pickupPoint->country = 'DK';

        $normalized = $this->serializer->normalize($pickupPoint);

        self::assertIsArray($normalized);
        self::assertSame(
            $this->encoder->encode(new PickupPointIdentifier('faker', '0', ['country' => 'DK'])),
            $normalized['identifier'],
        );
        // the delegated jsonSerialize() base shape is preserved alongside the added identifier
        self::assertSame('faker', $normalized['provider']);
        self::assertSame('DK', $normalized['country']);
    }

    public function testTheIdentifierIsNullWhenThePointCannotBeIdentified(): void
    {
        $normalized = $this->serializer->normalize(new PickupPoint());

        self::assertIsArray($normalized);
        self::assertNull($normalized['identifier']);
    }

    public function testItSupportsOnlyPickupPoints(): void
    {
        self::assertTrue($this->normalizer->supportsNormalization(new PickupPoint()));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
        self::assertSame([PickupPoint::class => false], $this->normalizer->getSupportedTypes('json'));
    }
}
