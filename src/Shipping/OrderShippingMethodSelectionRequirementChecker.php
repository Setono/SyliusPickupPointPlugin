<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Shipping;

use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Sylius\Component\Core\Checker\OrderShippingMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Shipping\Resolver\ShippingMethodsResolverInterface;

final readonly class OrderShippingMethodSelectionRequirementChecker implements OrderShippingMethodSelectionRequirementCheckerInterface
{
    public function __construct(
        private OrderShippingMethodSelectionRequirementCheckerInterface $decorated,
        private ShippingMethodsResolverInterface $shippingMethodsResolver,
    ) {
    }

    public function isShippingMethodSelectionRequired(OrderInterface $order): bool
    {
        if ($this->decorated->isShippingMethodSelectionRequired($order)) {
            return true;
        }

        // The original Sylius checker returns false in two cases
        // 1. If shipping is not required
        // 2. If there only is one available shipping method
        // We need to cover both cases here

        if (!$order->isShippingRequired()) {
            return false;
        }

        /** @var ShipmentInterface $shipment */
        foreach ($order->getShipments() as $shipment) {
            $supportedMethods = $this->shippingMethodsResolver->getSupportedMethods($shipment);
            if (count($supportedMethods) > 1) {
                return true;
            }

            /** @var ShippingMethodInterface $supportedMethod */
            foreach ($supportedMethods as $supportedMethod) {
                if ($supportedMethod->getPickupPointProvider() !== null) {
                    return true;
                }
            }
        }

        return false;
    }
}
