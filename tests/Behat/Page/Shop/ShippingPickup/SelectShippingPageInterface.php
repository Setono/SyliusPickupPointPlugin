<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Page\Shop\ShippingPickup;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Page\Shop\Checkout\SelectShippingPageInterface as BaseSelectShippingPageInterface;

interface SelectShippingPageInterface extends BaseSelectShippingPageInterface
{
    public function chooseFirstShippingPointFromDropdown(): void;
}
