<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Type;

use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder;
use Setono\SyliusPickupPointPlugin\Form\DataTransformer\PickupPointToIdentifierTransformer;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointType;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * Verifies — without booting the kernel — that the model transformer is attached to the form type,
 * so a submitted identifier token round-trips to a resolved {@see PickupPoint} object (which the
 * checkout then persists to the `pickup_point` column) and an existing point renders back as a token.
 */
final class PickupPointTypeTest extends TypeTestCase
{
    use ProphecyTrait;

    private PickupPointIdentifierEncoder $encoder;

    private PickupPoint $resolved;

    protected function setUp(): void
    {
        $this->encoder = new PickupPointIdentifierEncoder();

        $this->resolved = new PickupPoint();
        $this->resolved->provider = 'faker';
        $this->resolved->id = '0';
        $this->resolved->country = 'DK';

        parent::setUp();
    }

    public function testSubmittingATokenResolvesToThePickupPointObject(): void
    {
        $token = $this->encoder->encode(new PickupPointIdentifier('faker', '0', ['country' => 'DK']));

        $form = $this->factory->create(PickupPointType::class);
        $form->submit($token);

        self::assertTrue($form->isSynchronized());
        self::assertSame($this->resolved, $form->getData());
    }

    public function testAnExistingPickupPointRendersBackAsAToken(): void
    {
        $form = $this->factory->create(PickupPointType::class, $this->resolved);

        self::assertSame(
            $this->encoder->encode(new PickupPointIdentifier('faker', '0', ['country' => 'DK'])),
            $form->getViewData(),
        );
    }

    public function testSubmittingAMalformedTokenFailsTransformation(): void
    {
        $form = $this->factory->create(PickupPointType::class);
        $form->submit('!!! not a valid token !!!');

        self::assertFalse($form->isSynchronized());
        self::assertNull($form->getData());
    }

    protected function getExtensions(): array
    {
        $provider = $this->prophesize(ProviderInterface::class);
        $provider->findPickupPoint('0', ['country' => 'DK'])->willReturn($this->resolved);

        $registry = $this->prophesize(ProviderRegistryInterface::class);
        $registry->get('faker')->willReturn($provider->reveal());

        return [
            new PreloadedExtension([
                new PickupPointType(new PickupPointToIdentifierTransformer($registry->reveal(), $this->encoder)),
            ], []),
        ];
    }
}
