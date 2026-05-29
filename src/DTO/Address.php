<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DTO;

use Sylius\Component\Core\Model\OrderInterface;

/**
 * An immutable, order-agnostic query object describing the address a provider should search
 * pickup points near. Decoupling the providers from {@see OrderInterface} lets them be used
 * in non-checkout scenarios (e.g. an admin tool or a standalone lookup) where there is no order.
 *
 * All fields are nullable because a cart may not have a (complete) shipping address yet; each
 * provider decides which fields it requires and early-returns when a needed one is missing.
 */
final readonly class Address
{
    public function __construct(
        public ?string $street = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public ?string $countryCode = null,
    ) {
    }

    /**
     * Builds an {@see Address} from an order's shipping address. Returns an all-null instance when
     * the order has no shipping address yet, so providers see the same "missing field" shape they
     * would for a partially filled address.
     */
    public static function fromOrder(OrderInterface $order): self
    {
        $shippingAddress = $order->getShippingAddress();
        if (null === $shippingAddress) {
            return new self();
        }

        return new self(
            $shippingAddress->getStreet(),
            $shippingAddress->getPostcode(),
            $shippingAddress->getCity(),
            $shippingAddress->getCountryCode(),
        );
    }
}
