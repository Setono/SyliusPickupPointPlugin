<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPickupPointPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
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
        ShippingMethodInterface $shippingMethod,
        string $pickupPointProviderCode
    ): void {
        if (!$this->providerRegistry->has($pickupPointProviderCode)) {
            throw new RuntimeException(sprintf(
                'PickupPoint provider with code %s was not found in registry.',
                $pickupPointProviderCode
            ));
        }
        $shippingMethod->setPickupPointProvider($pickupPointProviderCode);
        $this->shippingMethodEntityManager->flush();
    }
}
