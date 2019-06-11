<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Core\Model\OrderInterface;

interface ProviderInterface
{
    /**
     * A unique code identifying this provider
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * Will return the name of this provider
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Will return an array of pickup points
     *
     * @param OrderInterface $order
     *
     * @return PickupPointInterface[]
     */
    public function findPickupPoints(OrderInterface $order): array;

    /**
     * @param string $id
     *
     * @return PickupPointInterface|null
     */
    public function findOnePickupPointById(string $id): ?PickupPointInterface;
}
