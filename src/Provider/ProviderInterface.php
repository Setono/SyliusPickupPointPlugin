<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Sylius\Component\Core\Model\OrderInterface;

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
     * Will return an array of pickup points
     *
     * @return list<PickupPoint>
     */
    public function findPickupPoints(OrderInterface $order): array;

    public function findPickupPoint(string $id, string $country): ?PickupPoint;
}
