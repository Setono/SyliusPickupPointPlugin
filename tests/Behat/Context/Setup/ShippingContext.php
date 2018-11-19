<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;

final class ShippingContext implements Context
{
    /** @var ProviderManagerInterface */
    private $providerManager;

    /** @var EntityManagerInterface */
    private $shippingMethodEntityManager;

    /**
     * @param ProviderManagerInterface $providerManager
     * @param EntityManagerInterface $shippingMethodEntityManager
     */
    public function __construct(
        ProviderManagerInterface $providerManager,
        EntityManagerInterface $shippingMethodEntityManager
    ) {
        $this->providerManager = $providerManager;
        $this->shippingMethodEntityManager = $shippingMethodEntityManager;
    }

    /**
     * @Given /^(shipping method "([^"]+)") has the selected "([^"]+)" pickup point provider$/
     */
    public function theShippingMethodHasTheSelectedGlsPickupPointProvider(
        PickupPointProviderAwareInterface $shippingMethod,
        string $namePickupPointProvider
    ): void {
        $provider = $this->providerManager->findByCode(strtolower($namePickupPointProvider));

        $shippingMethod->setPickupPointProvider(get_class($provider));

        $this->shippingMethodEntityManager->flush();
    }
}
