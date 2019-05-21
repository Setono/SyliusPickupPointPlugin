<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

interface PickupPointAwareInterface
{
    public function hasPickupPointId(): bool;

    public function setPickupPointId(?string $pickupPoint): void;

    public function getPickupPointId(): ?string;
}
