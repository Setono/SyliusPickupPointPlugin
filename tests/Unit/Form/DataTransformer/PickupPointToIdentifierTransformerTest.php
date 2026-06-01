<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;
use Setono\SyliusPickupPointPlugin\Exception\UnknownProviderException;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointToIdentifierTransformer;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class PickupPointToIdentifierTransformerTest extends TestCase
{
    use ProphecyTrait;

    private PickupPointIdentifierEncoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new PickupPointIdentifierEncoder();
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

    public function testItTransformsAPickupPointToAnEncodedToken(): void
    {
        $pickupPoint = new PickupPoint();
        $pickupPoint->provider = 'faker';
        $pickupPoint->id = '0';
        $pickupPoint->country = 'DK';

        self::assertSame(
            $this->encoder->encode(new PickupPointIdentifier('faker', '0', ['country' => 'DK'])),
            $this->transformer()->transform($pickupPoint),
        );
    }

    public function testItTransformsAPickupPointWithoutProviderOrIdToNull(): void
    {
        self::assertNull($this->transformer()->transform(new PickupPoint()));
    }

    public function testItReverseTransformsNullToNull(): void
    {
        self::assertNull($this->transformer()->reverseTransform(null));
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

    public function testItReverseTransformsAValidTokenToTheResolvedPickupPoint(): void
    {
        $resolved = new PickupPoint();
        $resolved->provider = 'faker';
        $resolved->id = '0';

        $provider = $this->prophesize(ProviderInterface::class);
        $provider->findPickupPoint('0', ['country' => 'DK'])->willReturn($resolved);

        $registry = $this->prophesize(ProviderRegistryInterface::class);
        $registry->get('faker')->willReturn($provider->reveal());

        $token = $this->encoder->encode(new PickupPointIdentifier('faker', '0', ['country' => 'DK']));

        self::assertSame(
            $resolved,
            (new PickupPointToIdentifierTransformer($registry->reveal(), $this->encoder))->reverseTransform($token),
        );
    }

    public function testItThrowsReverseTransformingATokenForAnUnknownProvider(): void
    {
        $registry = $this->prophesize(ProviderRegistryInterface::class);
        $registry->get('faker')->willThrow(new UnknownProviderException('faker', []));

        $token = $this->encoder->encode(new PickupPointIdentifier('faker', '0'));

        $this->expectException(TransformationFailedException::class);

        (new PickupPointToIdentifierTransformer($registry->reveal(), $this->encoder))->reverseTransform($token);
    }

    private function transformer(): PickupPointToIdentifierTransformer
    {
        return new PickupPointToIdentifierTransformer(
            $this->prophesize(ProviderRegistryInterface::class)->reveal(),
            $this->encoder,
        );
    }
}
