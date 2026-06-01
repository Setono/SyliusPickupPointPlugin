<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Encoder;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoder;

final class PickupPointEncoderTest extends TestCase
{
    private PickupPointEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PickupPointEncoder();
    }

    public function testItRoundTripsAFullPickupPoint(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'faker';
        $pickupPoint->id = '0';
        $pickupPoint->name = 'Post office #0';
        $pickupPoint->address = 'Main Street 1';
        $pickupPoint->zipCode = '9000';
        $pickupPoint->city = 'Aalborg';
        $pickupPoint->country = 'DK';
        $pickupPoint->latitude = '57.0488';
        $pickupPoint->longitude = '9.9217';
        $pickupPoint->metadata = ['warehouse' => '42'];

        self::assertEquals($pickupPoint, $this->encoder->decode($this->encoder->encode($pickupPoint)));
    }

    public function testItProducesAUrlSafeTokenEvenForNonAsciiData(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->name = 'Æøå Pakkeshop café';

        self::assertSame(1, preg_match('/^[A-Za-z0-9\-_]+$/', $this->encoder->encode($pickupPoint)));
    }

    public function testItThrowsDecodingInvalidBase64(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->encoder->decode('!!! not base64 !!!');
    }

    public function testItThrowsDecodingATokenThatIsNotAnObject(): void
    {
        $token = rtrim(strtr(base64_encode('123'), '+/', '-_'), '=');

        $this->expectException(InvalidArgumentException::class);

        $this->encoder->decode($token);
    }
}
