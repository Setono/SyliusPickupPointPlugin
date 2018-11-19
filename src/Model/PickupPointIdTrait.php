<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

trait PickupPointIdTrait
{
    /**
     * @var string|null
     */
    protected $pickupPointId;

    /**
     * @return bool
     */
    public function hasPickupPointId(): bool
    {
        return $this->pickupPointId !== null;
    }

    /**
     * @param string|null $pickupPointId
     */
    public function setPickupPointId(?string $pickupPointId): void
    {
        $this->pickupPointId = $pickupPointId;
    }

    /**
     * @return string|null
     */
    public function getPickupPointId(): ?string
    {
        return $this->pickupPointId;
    }
}
