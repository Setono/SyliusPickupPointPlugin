<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

trait PickupPointProviderTrait
{
    /**
     * @var string|null
     */
    protected $pickupPointProvider;

    /**
     * Returns true if this object has an associated pickup provider
     *
     * @return bool
     */
    public function hasPickupPointProvider(): bool
    {
        return $this->pickupPointProvider !== null;
    }

    /**
     * @param string|null $pickupPointProvider
     */
    public function setPickupPointProvider(?string $pickupPointProvider): void
    {
        $this->pickupPointProvider = $pickupPointProvider;
    }

    /**
     * Returns the class name of the pickup provider
     *
     * @return string|null
     */
    public function getPickupPointProvider(): ?string
    {
        return $this->pickupPointProvider;
    }
}
