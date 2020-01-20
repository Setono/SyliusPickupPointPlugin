<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Repository;

use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

interface PickupPointRepositoryInterface extends RepositoryInterface
{
    public function findOneByProviderIdAndProviderAndCountry(
        string $providerId,
        string $provider,
        string $country
    ): ?PickupPointInterface;
}
