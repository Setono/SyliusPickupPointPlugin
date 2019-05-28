<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Sylius\Component\Core\Model\ShipmentInterface as BaseShipmentInterface;

interface ShipmentInterface extends BaseShipmentInterface, PickupPointAwareInterface
{
}
