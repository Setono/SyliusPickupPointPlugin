<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

trait PickupPointAwareTrait
{
    /**
     * @var string|null
     */
    protected $pickupPointId;

    public function hasPickupPointId(): bool
    {
        return null !== $this->pickupPointId;
    }

    public function setPickupPointId(?string $pickupPointId): void
    {
        $this->pickupPointId = $pickupPointId;
    }

    public function getPickupPointId(): ?string
    {
        return $this->pickupPointId;
    }
}
