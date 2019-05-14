<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

trait PickupPointIdTrait
{
    /**
     * @var string|null
     */
    protected $pickupPointId;

    public function hasPickupPointId(): bool
    {
        return $this->pickupPointId !== null;
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
