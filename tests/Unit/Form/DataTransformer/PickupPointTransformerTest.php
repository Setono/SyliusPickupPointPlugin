<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoder;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class PickupPointTransformerTest extends TestCase
{
    private PickupPointEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PickupPointEncoder();
    }

    public function testItTransformsNullToNull(): void
    {
        self::assertNull($this->transformer()->transform(null));
    }

    public function testItThrowsTransformingANonPickupPoint(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer()->transform('not a pickup point');
    }

    public function testItTransformsAPickupPointToAToken(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'faker';
        $pickupPoint->id = '0';

        self::assertSame($this->encoder->encode($pickupPoint), $this->transformer()->transform($pickupPoint));
    }

    public function testItReverseTransformsNullToNull(): void
    {
        self::assertNull($this->transformer()->reverseTransform(null));
    }

    public function testItReverseTransformsAnEmptyStringToNull(): void
    {
        self::assertNull($this->transformer()->reverseTransform(''));
    }

    public function testItThrowsReverseTransformingANonString(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer()->reverseTransform(123);
    }

    public function testItThrowsReverseTransformingAMalformedToken(): void
    {
        $this->expectException(TransformationFailedException::class);

        $this->transformer()->reverseTransform('!!! not a valid token !!!');
    }

    public function testItReverseTransformsATokenBackToTheSamePickupPoint(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'faker';
        $pickupPoint->id = '0';
        $pickupPoint->name = 'Post office #0';
        $pickupPoint->city = 'Aalborg';

        self::assertEquals(
            $pickupPoint,
            $this->transformer()->reverseTransform($this->encoder->encode($pickupPoint)),
        );
    }

    private function transformer(): PickupPointTransformer
    {
        return new PickupPointTransformer($this->encoder);
    }
}
