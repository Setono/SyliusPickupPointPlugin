<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;

interface PickupPointAwareInterface
{
    /**
     * @deprecated since 2.0, use {@see hasPickupPoint()} instead
     */
    public function hasPickupPointId(): bool;

    /**
     * @deprecated since 2.0, use {@see setPickupPoint()} instead
     */
    public function setPickupPointId(?string $pickupPoint): void;

    /**
     * @deprecated since 2.0, use {@see getPickupPoint()} instead
     */
    public function getPickupPointId(): ?string;

    public function hasPickupPoint(): bool;

    public function setPickupPoint(?PickupPoint $pickupPoint): void;

    public function getPickupPoint(): ?PickupPoint;
}
