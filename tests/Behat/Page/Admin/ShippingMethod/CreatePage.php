<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Page\Admin\ShippingMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

final class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function selectPickupPointProvider(string $providerCode): void
    {
        $this->getDocument()->selectFieldOption('sylius_shipping_method_pickupPointProvider', $providerCode);
    }
}
