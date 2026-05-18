<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShipmentTypeExtension;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ShipmentTypeExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsAnAbstractTypeExtension(): void
    {
        self::assertInstanceOf(AbstractTypeExtension::class, new ShipmentTypeExtension());
    }

    public function testItBuildsFormWithoutErrors(): void
    {
        $extension = new ShipmentTypeExtension();

        $builder = $this->prophesize(FormBuilderInterface::class);
        $builder->add(Argument::type('string'), Argument::type('string'), Argument::any())->willReturn($builder->reveal());

        $extension->buildForm($builder->reveal(), []);
    }
}
