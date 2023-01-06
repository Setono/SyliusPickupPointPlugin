<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Factory;

use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;

interface PickupPointFactoryInterface
{
    public function createNew(): PickupPointInterface;
}
