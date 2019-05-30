<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\Constraint;

final class HasPickupPointSelectedSpec extends ObjectBehavior
{
    public function it_is_a_constraint()
    {
        $this->shouldHaveType(Constraint::class);
    }

    public function it_has_validator()
    {
        $this->validatedBy()->shouldReturn('setono_pickup_point_has_pickup_point_selected');
    }

    public function it_has_a_target()
    {
        $this->getTargets()->shouldReturn('class');
    }

    function it_has_a_message(): void
    {
        $this->pickupPointNotBlank->shouldReturn('setono_pickup_point.shipment.pickup_point.not_blank');
    }
}
