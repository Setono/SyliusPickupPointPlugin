<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Sylius\Component\Registry\ServiceRegistryInterface;
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
            ['Sylius\\Bundle\\ShippingBundle\\Form\\Type\\ShippingMethodChoiceType'],
            iterator_to_array((function () {
                yield from ShippingMethodChoiceTypeExtension::getExtendedTypes();
            })()),
        );
    }

    private function createExtension(): ShippingMethodChoiceTypeExtension
    {
        return new ShippingMethodChoiceTypeExtension(
            $this->prophesize(ServiceRegistryInterface::class)->reveal(),
        );
    }
}
