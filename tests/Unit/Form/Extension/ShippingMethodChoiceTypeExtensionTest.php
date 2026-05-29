<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;

final class ShippingMethodChoiceTypeExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsAnAbstractTypeExtension(): void
    {
        $extension = $this->createExtension();

        self::assertInstanceOf(AbstractTypeExtension::class, $extension);
    }

    public function testItExtendsShippingMethodChoiceType(): void
    {
        self::assertSame(
            [ShippingMethodChoiceType::class],
            iterator_to_array((function () {
                yield from ShippingMethodChoiceTypeExtension::getExtendedTypes();
            })()),
        );
    }

    private function createExtension(): ShippingMethodChoiceTypeExtension
    {
        return new ShippingMethodChoiceTypeExtension(
            $this->prophesize(ProviderRegistryInterface::class)->reveal(),
        );
    }
}
