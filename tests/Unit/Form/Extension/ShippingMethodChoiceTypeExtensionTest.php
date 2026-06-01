<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Form\Extension;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\Form\Extension\ShippingMethodChoiceTypeExtension;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use Sylius\Bundle\ShippingBundle\Form\Type\ShippingMethodChoiceType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ShippingMethodChoiceTypeExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsAnAbstractTypeExtension(): void
    {
        self::assertInstanceOf(AbstractTypeExtension::class, $this->createExtension());
    }

    public function testItExtendsShippingMethodChoiceType(): void
    {
        self::assertContains(ShippingMethodChoiceType::class, ShippingMethodChoiceTypeExtension::getExtendedTypes());
    }

    public function testItStampsTheProviderCodeOnPickupCapableMethods(): void
    {
        $registry = $this->prophesize(ProviderRegistryInterface::class);
        $registry->has('faker')->willReturn(true);

        $method = $this->prophesize(PickupPointProviderAwareInterface::class);
        $method->hasPickupPointProvider()->willReturn(true);
        $method->getPickupPointProvider()->willReturn('faker');

        self::assertSame(
            ['data-pickup-point-provider' => 'faker'],
            ($this->choiceAttr($registry->reveal()))($method->reveal()),
        );
    }

    public function testItStampsNothingOnMethodsWithoutAProvider(): void
    {
        $method = $this->prophesize(PickupPointProviderAwareInterface::class);
        $method->hasPickupPointProvider()->willReturn(false);

        self::assertSame([], ($this->choiceAttr($this->prophesize(ProviderRegistryInterface::class)->reveal()))($method->reveal()));
    }

    private function createExtension(): ShippingMethodChoiceTypeExtension
    {
        return new ShippingMethodChoiceTypeExtension(
            $this->prophesize(ProviderRegistryInterface::class)->reveal(),
        );
    }

    private function choiceAttr(ProviderRegistryInterface $registry): callable
    {
        $resolver = new OptionsResolver();
        $resolver->setDefined('choice_attr');
        (new ShippingMethodChoiceTypeExtension($registry))->configureOptions($resolver);

        $choiceAttr = $resolver->resolve()['choice_attr'];
        self::assertIsCallable($choiceAttr);

        return $choiceAttr;
    }
}
