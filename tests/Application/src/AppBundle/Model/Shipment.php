<?php

declare(strict_types=1);

namespace AppBundle\Model;

use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareTrait;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

class Shipment extends BaseShipment implements PickupPointAwareInterface
{
    use PickupPointAwareTrait;
}
