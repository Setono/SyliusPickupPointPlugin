<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Setono\SyliusPickupPointPlugin\Tests\Behat\Page\Admin\ShippingMethod\CreatePageInterface;

final class ShippingContext implements Context
{
    private CreatePageInterface $createPage;

    public function __construct(CreatePageInterface $createPage)
    {
        $this->createPage = $createPage;
    }

    /**
     * @When I select :providerCode as pickup point provider
     */
    public function iSelectAsPickupPointProvider(string $providerCode): void
    {
        $this->createPage->selectPickupPointProvider($providerCode);
    }
}
