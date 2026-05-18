<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ShippingMethodChoiceTypeExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsAnAbstractTypeExtension(): void
    {
        $extension = $this->createExtension();

        self::assertInstanceOf(AbstractTypeExtension::class, $extension);
    }

    public function testItBuildsFormWithoutErrors(): void
    {
        $extension = $this->createExtension();

        $builder = $this->prophesize(FormBuilderInterface::class);
        $builder->add(Argument::type('string'), Argument::type('string'), Argument::any())->willReturn($builder->reveal());

        $extension->buildForm($builder->reveal(), []);
    }

    private function createExtension(): ShippingMethodChoiceTypeExtension
    {
        return new ShippingMethodChoiceTypeExtension(
            $this->prophesize(ServiceRegistryInterface::class)->reveal(),
            $this->prophesize(CartContextInterface::class)->reveal(),
            $this->prophesize(CsrfTokenManagerInterface::class)->reveal(),
        );
    }
}
