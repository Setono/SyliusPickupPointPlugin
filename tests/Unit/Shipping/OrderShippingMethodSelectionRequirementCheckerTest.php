<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Shipping;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Setono\SyliusPickupPointPlugin\Shipping\OrderShippingMethodSelectionRequirementChecker;
use Sylius\Component\Core\Checker\OrderShippingMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;

final class OrderShippingMethodSelectionRequirementCheckerTest extends TestCase
{
    public function testItReturnsTrueWhenTheDecoratedCheckerRequiresSelection(): void
    {
        $order = $this->createMock(OrderInterface::class);

        $decorated = $this->createMock(OrderShippingMethodSelectionRequirementCheckerInterface::class);
        $decorated->method('isShippingMethodSelectionRequired')->with($order)->willReturn(true);

        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        // The decorated checker short-circuits, so the resolver must never be consulted
        $resolver->expects(self::never())->method('getSupportedMethods');
        // ...and neither should the order be inspected further
        $order->expects(self::never())->method('isShippingRequired');

        $checker = new OrderShippingMethodSelectionRequirementChecker($decorated, $resolver);

        self::assertTrue($checker->isShippingMethodSelectionRequired($order));
    }

    public function testItReturnsFalseWhenShippingIsNotRequired(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('isShippingRequired')->willReturn(false);

        $decorated = $this->createMock(OrderShippingMethodSelectionRequirementCheckerInterface::class);
        $decorated->method('isShippingMethodSelectionRequired')->with($order)->willReturn(false);

        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        // Shipping is not required, so we never look at shipments/methods
        $resolver->expects(self::never())->method('getSupportedMethods');
        $order->expects(self::never())->method('getShipments');

        $checker = new OrderShippingMethodSelectionRequirementChecker($decorated, $resolver);

        self::assertFalse($checker->isShippingMethodSelectionRequired($order));
    }

    public function testItReturnsTrueWhenAShipmentHasMoreThanOneSupportedMethod(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);

        $order = $this->createMock(OrderInterface::class);
        $order->method('isShippingRequired')->willReturn(true);
        $order->method('getShipments')->willReturn(new ArrayCollection([$shipment]));

        $decorated = $this->createMock(OrderShippingMethodSelectionRequirementCheckerInterface::class);
        $decorated->method('isShippingMethodSelectionRequired')->with($order)->willReturn(false);

        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        $resolver->method('getSupportedMethods')->with($shipment)->willReturn([
            $this->createMock(ShippingMethodInterface::class),
            $this->createMock(ShippingMethodInterface::class),
        ]);

        $checker = new OrderShippingMethodSelectionRequirementChecker($decorated, $resolver);

        self::assertTrue($checker->isShippingMethodSelectionRequired($order));
    }

    public function testItReturnsTrueWhenTheSingleSupportedMethodHasAPickupPointProvider(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);

        $order = $this->createMock(OrderInterface::class);
        $order->method('isShippingRequired')->willReturn(true);
        $order->method('getShipments')->willReturn(new ArrayCollection([$shipment]));

        $decorated = $this->createMock(OrderShippingMethodSelectionRequirementCheckerInterface::class);
        $decorated->method('isShippingMethodSelectionRequired')->with($order)->willReturn(false);

        $method = $this->createMock(ShippingMethodInterface::class);
        $method->method('getPickupPointProvider')->willReturn('faker');

        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        $resolver->method('getSupportedMethods')->with($shipment)->willReturn([$method]);

        $checker = new OrderShippingMethodSelectionRequirementChecker($decorated, $resolver);

        self::assertTrue($checker->isShippingMethodSelectionRequired($order));
    }

    public function testItReturnsFalseWhenTheSingleSupportedMethodHasNoPickupPointProvider(): void
    {
        $shipment = $this->createMock(ShipmentInterface::class);

        $order = $this->createMock(OrderInterface::class);
        $order->method('isShippingRequired')->willReturn(true);
        $order->method('getShipments')->willReturn(new ArrayCollection([$shipment]));

        $decorated = $this->createMock(OrderShippingMethodSelectionRequirementCheckerInterface::class);
        $decorated->method('isShippingMethodSelectionRequired')->with($order)->willReturn(false);

        $method = $this->createMock(ShippingMethodInterface::class);
        $method->method('getPickupPointProvider')->willReturn(null);

        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        $resolver->method('getSupportedMethods')->with($shipment)->willReturn([$method]);

        $checker = new OrderShippingMethodSelectionRequirementChecker($decorated, $resolver);

        self::assertFalse($checker->isShippingMethodSelectionRequired($order));
    }

    public function testItReturnsFalseWhenThereAreNoShipments(): void
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('isShippingRequired')->willReturn(true);
        $order->method('getShipments')->willReturn(new ArrayCollection([]));

        $decorated = $this->createMock(OrderShippingMethodSelectionRequirementCheckerInterface::class);
        $decorated->method('isShippingMethodSelectionRequired')->with($order)->willReturn(false);

        $resolver = $this->createMock(ShippingMethodsResolverInterface::class);
        $resolver->expects(self::never())->method('getSupportedMethods');

        $checker = new OrderShippingMethodSelectionRequirementChecker($decorated, $resolver);

        self::assertFalse($checker->isShippingMethodSelectionRequired($order));
    }
}
