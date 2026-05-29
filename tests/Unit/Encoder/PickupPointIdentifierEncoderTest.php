<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Encoder;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;

final class PickupPointIdentifierEncoderTest extends TestCase
{
    private PickupPointIdentifierEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PickupPointIdentifierEncoder();
    }

    public function testItRoundTripsAnIdentifier(): void
    {
        $decoded = $this->encoder->decode(
            $this->encoder->encode(new PickupPointIdentifier('gls', '12345', ['country' => 'DK'])),
        );

        self::assertSame('gls', $decoded->provider);
        self::assertSame('12345', $decoded->id);
        self::assertSame(['country' => 'DK'], $decoded->metadata);
    }

    public function testItRoundTripsArbitraryMetadata(): void
    {
        $metadata = ['country' => 'DK', 'region' => 'north', 'warehouse' => 7];

        $decoded = $this->encoder->decode(
            $this->encoder->encode(new PickupPointIdentifier('acme', 'a-b/c', $metadata)),
        );

        self::assertSame('acme', $decoded->provider);
        self::assertSame('a-b/c', $decoded->id);
        self::assertSame($metadata, $decoded->metadata);
    }

    public function testItProducesAUrlSafeToken(): void
    {
        $token = $this->encoder->encode(new PickupPointIdentifier('gls', '12345', ['country' => 'DK']));

        self::assertSame(1, preg_match('/^[A-Za-z0-9\-_]+$/', $token));
    }

    public function testItDefaultsToEmptyMetadata(): void
    {
        $decoded = $this->encoder->decode($this->encoder->encode(new PickupPointIdentifier('gls', '12345')));

        self::assertSame([], $decoded->metadata);
    }

    public function testItThrowsWhenDecodingAMalformedToken(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->encoder->decode('!!! not base64 or json !!!');
    }

    public function testItThrowsWhenTheTokenLacksProviderAndId(): void
    {
        $json = json_encode(['foo' => 'bar'], \JSON_THROW_ON_ERROR);
        $token = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');

        $this->expectException(InvalidArgumentException::class);

        $this->encoder->decode($token);
    }
}
