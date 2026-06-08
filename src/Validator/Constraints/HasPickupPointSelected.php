<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

final class HasPickupPointSelected extends Constraint
{
    public string $pickupPointNotBlank = 'setono_pickup_point.shipment.pickup_point.not_blank';

    public function getTargets(): string
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
