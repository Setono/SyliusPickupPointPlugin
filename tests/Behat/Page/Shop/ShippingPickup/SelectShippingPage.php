<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Page\Shop\ShippingPickup;

use Sylius\Behat\Page\Shop\Checkout\SelectShippingPage as BaseSelectShippingPage;

final class SelectShippingPage extends BaseSelectShippingPage implements SelectShippingPageInterface
{
    public function chooseFirstShippingPointFromDropdown(): void
    {
        $this->getDocument()->waitFor(5, function () {
            return $this->hasElement('pickup_point_dropdown');
        });

        $dropdown = $this->getElement('pickup_point_dropdown');

        $dropdown->click();

        $item = $this->getElement('pickup_point_dropdown_item', [
            '%value%' => '001',
        ]);

        $item->click();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'pickup_point_dropdown' => '.pickup-point-dropdown',
            'pickup_point_dropdown_item' => '.pickup-point-dropdown div.item[data-value="%value%"]',
        ]);
    }
}
