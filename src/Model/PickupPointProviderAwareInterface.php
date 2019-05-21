<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointProviderAwareInterface
{
    public function hasPickupPointProvider(): bool;

    /**
     * Returns the code of the pickup provider
     *
     * Returns null if no pickup point provider is available
     *
     * @return string
     */
    public function getPickupPointProvider(): ?string;

    public function setPickupPointProvider(?string $pickupPointProvider): void;
}
