<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Factory;

use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;

final class PickupPointFactory implements PickupPointFactoryInterface
{
    public function createNew(): PickupPointInterface
    {
        return new PickupPoint();
    }
}
