<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointProviderAwareInterface
{
    /**
     * Returns true if this object has an associated pickup provider
     *
     * @return bool
     */
    public function hasPickupPointProvider(): bool;

    /**
     * Returns the class name of the pickup provider
     *
     * Returns null if no pickup point provider is available
     *
     * @return string
     */
    public function getPickupPointProvider(): ?string;

    /**
     * @param string|null $pickupPointProvider
     */
    public function setPickupPointProvider(?string $pickupPointProvider): void;
}
