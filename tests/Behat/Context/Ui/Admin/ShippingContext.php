<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\Setono\SyliusPickupPointPlugin\Behat\Page\Admin\ShippingMethod\CreatePageInterface;

final class ShippingContext implements Context
{
    /** @var CreatePageInterface */
    private $createPage;

    public function __construct(
        CreatePageInterface $createPage
    ) {
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
