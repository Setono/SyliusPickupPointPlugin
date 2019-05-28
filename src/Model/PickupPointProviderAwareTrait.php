<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait PickupPointProviderAwareTrait
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $pickupPointProvider;

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
