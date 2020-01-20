<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPoint;
use Setono\SyliusPickupPointPlugin\PickupPoint\PickupPointId;
use Sylius\Component\Core\Model\OrderInterface;

interface ProviderInterface
{
    /**
     * A unique code identifying this provider
     */
    public function getCode(): string;

    /**
     * Will return the name of this provider
     */
    public function getName(): string;

    /**
     * Will return an array of pickup points
     *
     * @return iterable<PickupPoint>
     */
    public function findPickupPoints(OrderInterface $order): iterable;

    public function findPickupPoint(PickupPointId $id): ?PickupPoint;

    /**
     * Returns all pickup points for this provider
     *
     * @return iterable<PickupPoint>
     */
    public function findAllPickupPoints(): iterable;
}
