<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Behat\Page\Admin\ShippingMethod;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function selectPickupPointProvider(string $providerCode): void;
}
