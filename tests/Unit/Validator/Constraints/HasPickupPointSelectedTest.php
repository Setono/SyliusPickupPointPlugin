<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Validator\Constraints\HasPickupPointSelected;
use Symfony\Component\Validator\Constraint;

final class HasPickupPointSelectedTest extends TestCase
{
    public function testItIsAConstraint(): void
    {
        self::assertInstanceOf(Constraint::class, new HasPickupPointSelected());
    }

    public function testItHasValidator(): void
    {
        self::assertSame(
            'setono_pickup_point_has_pickup_point_selected',
            (new HasPickupPointSelected())->validatedBy(),
        );
    }

    public function testItHasClassTarget(): void
    {
        self::assertSame(Constraint::CLASS_CONSTRAINT, (new HasPickupPointSelected())->getTargets());
    }

    public function testItHasMessage(): void
    {
        self::assertSame(
            'setono_pickup_point.shipment.pickup_point.not_blank',
            (new HasPickupPointSelected())->pickupPointNotBlank,
        );
    }
}
