<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;

interface ProviderInterface
{
    /**
     * Sets the code this provider is registered under. Called once by the container
     * (wired by the RegisterProvidersPass) so the provider can stamp it onto the
     * pickup points it returns. The code is resolved at compile time — either from
     * the `#[AsProvider]` attribute or from the service tag's `code` attribute.
     */
    public function setCode(string $code): void;

    /**
     * Returns the pickup points near the given address.
     *
     * Takes an {@see Address} rather than an order so providers can be used outside the checkout
     * flow (e.g. an admin tool or a standalone lookup). Build one from an order with
     * {@see Address::fromOrder()}.
     *
     * @return list<PickupPoint>
     */
    public function findPickupPoints(Address $address): array;

    /**
     * Resolves a single pickup point from its provider-local id.
     *
     * @param array<string, mixed> $metadata extra, carrier-specific context needed to resolve the
     *                                        pickup point (e.g. `['country' => 'DK']`). Untyped on
     *                                        purpose: the data required to look up a pickup point by
     *                                        id varies per carrier, so this stays an open SPI hook.
     */
    public function findPickupPoint(string $id, array $metadata = []): ?PickupPoint;
}
