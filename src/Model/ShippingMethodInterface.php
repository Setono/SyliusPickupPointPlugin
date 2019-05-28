<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Sylius\Component\Core\Model\ShippingMethodInterface as BaseShippingMethodInterface;

interface ShippingMethodInterface extends BaseShippingMethodInterface, PickupPointProviderAwareInterface
{
}
