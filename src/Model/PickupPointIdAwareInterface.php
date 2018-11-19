<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointIdAwareInterface
{
    /**
     * Returns true is the object has a pickup point id
     *
     * @return bool
     */
    public function hasPickupPointId(): bool;

    /**
     * Sets the id of the pickup point
     *
     * @param string|null $pickupPoint
     */
    public function setPickupPointId(?string $pickupPoint): void;

    /**
     * @return string|null
     */
    public function getPickupPointId(): ?string;
}
