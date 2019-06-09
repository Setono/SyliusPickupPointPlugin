<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class ShippingContext implements Context
{
    /** @var ServiceRegistryInterface */
    private $providerRegistry;

    /** @var EntityManagerInterface */
    private $shippingMethodEntityManager;

    public function __construct(
        ServiceRegistryInterface $providerRegistry,
        EntityManagerInterface $shippingMethodEntityManager
    ) {
        $this->providerRegistry = $providerRegistry;
        $this->shippingMethodEntityManager = $shippingMethodEntityManager;
    }

    /**
     * @Given /^(shipping method "[^"]+") has the selected "([^"]+)" pickup point provider$/
     */
    public function theShippingMethodHasTheSelectedGlsPickupPointProvider(
        PickupPointProviderAwareInterface $shippingMethod,
        string $namePickupPointProvider
    ): void {
        $provider = $this->providerRegistry->get(strtolower($namePickupPointProvider));

        $shippingMethod->setPickupPointProvider(get_class($provider));

        $this->shippingMethodEntityManager->flush();
    }
}
