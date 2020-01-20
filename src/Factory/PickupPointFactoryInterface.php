<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Factory;

use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

interface PickupPointFactoryInterface extends FactoryInterface
{
    public function createNew(
        PickupPointCode $code = null,
        string $name = null,
        string $address = null,
        string $zipCode = null,
        string $city = null,
        string $country = null,
        string $latitude = null,
        string $longitude = null
    ): PickupPointInterface;
}
