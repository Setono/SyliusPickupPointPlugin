<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait PickupPointProviderAwareTrait
{
    #[ORM\Column(name: 'pickup_point_provider', type: Types::STRING, nullable: true)]
    protected ?string $pickupPointProvider = null;

    public function hasPickupPointProvider(): bool
    {
        return $this->pickupPointProvider !== null;
    }

    public function setPickupPointProvider(?string $pickupPointProvider): void
    {
        $this->pickupPointProvider = $pickupPointProvider;
    }

    public function getPickupPointProvider(): ?string
    {
        return $this->pickupPointProvider;
    }
}
