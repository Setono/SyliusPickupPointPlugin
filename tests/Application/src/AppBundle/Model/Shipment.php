<?php

declare(strict_types=1);

namespace AppBundle\Model;

use Setono\SyliusPickupPointPlugin\Model\PickupPointIdAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointIdTrait;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

class Shipment extends BaseShipment implements PickupPointIdAwareInterface
{
    use PickupPointIdTrait;
}
