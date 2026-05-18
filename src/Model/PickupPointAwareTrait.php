<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;

trait PickupPointAwareTrait
{
    /** @deprecated since 2.0, use the {@see $pickupPoint} column instead */
    #[ORM\Column(name: 'pickup_point_id', type: Types::STRING, nullable: true)]
    protected ?string $pickupPointId = null;

    /** @var array<string, mixed>|PickupPoint|null */
    #[ORM\Column(name: 'pickup_point', type: Types::JSON, nullable: true)]
    protected null|PickupPoint|array $pickupPoint = null;

    /**
     * @deprecated since 2.0, use {@see hasPickupPoint()} instead
     */
    public function hasPickupPointId(): bool
    {
        return null !== $this->pickupPointId;
    }

    /**
     * @deprecated since 2.0, use {@see setPickupPoint()} instead
     */
    public function setPickupPointId(?string $pickupPointId): void
    {
        $this->pickupPointId = $pickupPointId;
    }

    /**
     * @deprecated since 2.0, use {@see getPickupPoint()} instead
     */
    public function getPickupPointId(): ?string
    {
        return $this->pickupPointId;
    }

    public function hasPickupPoint(): bool
    {
        return null !== $this->pickupPoint;
    }

    public function setPickupPoint(?PickupPoint $pickupPoint): void
    {
        $this->pickupPoint = $pickupPoint;
    }

    public function getPickupPoint(): ?PickupPoint
    {
        if (null === $this->pickupPoint) {
            return null;
        }

        if ($this->pickupPoint instanceof PickupPoint) {
            return $this->pickupPoint;
        }

        return PickupPoint::fromArray($this->pickupPoint);
    }
}
