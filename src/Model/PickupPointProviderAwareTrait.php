<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

trait PickupPointProviderAwareTrait
{
    /**
     * @var string|null
     */
    protected $pickupPointProvider;

    public function hasPickupPointProvider(): bool
    {
        return $this->pickupPointProvider !== null;
    }

    public function setPickupPointProvider(?string $pickupPointProvider): void
    {
        $this->pickupPointProvider = $pickupPointProvider;
    }

    public function getPickupPointProvider(): ?string
    {
        return $this->pickupPointProvider;
    }
}
