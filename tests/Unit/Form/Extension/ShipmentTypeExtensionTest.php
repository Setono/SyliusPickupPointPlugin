<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShipmentTypeExtension;
use Setono\SyliusPickupPointPlugin\Form\Type\PickupPointType;
use Sylius\Bundle\CoreBundle\Form\Type\Checkout\ShipmentType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ShipmentTypeExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsAnAbstractTypeExtension(): void
    {
        self::assertInstanceOf(AbstractTypeExtension::class, new ShipmentTypeExtension());
    }

    public function testItExtendsTheCheckoutShipmentType(): void
    {
        self::assertContains(ShipmentType::class, ShipmentTypeExtension::getExtendedTypes());
    }

    public function testItAddsThePickupPointField(): void
    {
        $builder = $this->prophesize(FormBuilderInterface::class);
        $builder->add('pickupPoint', PickupPointType::class, Argument::type('array'))
            ->shouldBeCalled()
            ->willReturn($builder->reveal());

        (new ShipmentTypeExtension())->buildForm($builder->reveal(), []);
    }
}
