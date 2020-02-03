<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface ProviderInterface
{
    public function __toString(): string;

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
     * @return iterable<PickupPointInterface>
     */
    public function findPickupPoints(OrderInterface $order): iterable;

    public function findPickupPoint(PickupPointCode $code): ?PickupPointInterface;

    /**
     * Returns all pickup points for this provider
     *
     * @return iterable<PickupPointInterface>|PickupPointInterface[]
     */
    public function findAllPickupPoints(): iterable;
}
