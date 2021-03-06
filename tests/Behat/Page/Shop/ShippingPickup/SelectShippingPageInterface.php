<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Page\Shop\ShippingPickup;

use Sylius\Behat\Page\Shop\Checkout\SelectShippingPageInterface as BaseSelectShippingPageInterface;

interface SelectShippingPageInterface extends BaseSelectShippingPageInterface
{
    public function selectPickupPointShippingMethod(string $shippingMethod): void;

    public function chooseFirstShippingPointFromRadio(): void;
}
