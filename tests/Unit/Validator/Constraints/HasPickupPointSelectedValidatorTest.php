<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Validator\Constraints;

use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Setono\SyliusPickupPointPlugin\Model\ShipmentInterface;
use Setono\SyliusPickupPointPlugin\Model\ShippingMethodInterface;
use Setono\SyliusPickupPointPlugin\Validator\Constraints\HasPickupPointSelected;
use Setono\SyliusPickupPointPlugin\Validator\Constraints\HasPickupPointSelectedValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class HasPickupPointSelectedValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    public function testItDoesNotViolateWhenTheMethodHasNoPickupPointProvider(): void
    {
        $this->validator->validate($this->shipment(hasProvider: false, hasPickupPoint: false)->reveal(), new HasPickupPointSelected());

        $this->assertNoViolation();
    }

    public function testItDoesNotViolateWhenAPickupPointIsSelected(): void
    {
        $this->validator->validate($this->shipment(hasProvider: true, hasPickupPoint: true)->reveal(), new HasPickupPointSelected());

        $this->assertNoViolation();
    }

    public function testItViolatesWhenTheProviderIsSetButNoPickupPointIsSelected(): void
    {
        $constraint = new HasPickupPointSelected();

        $this->validator->validate($this->shipment(hasProvider: true, hasPickupPoint: false)->reveal(), $constraint);

        $this->buildViolation($constraint->pickupPointNotBlank)->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        return new HasPickupPointSelectedValidator();
    }

    /**
     * @return ObjectProphecy<ShipmentInterface>
     */
    private function shipment(bool $hasProvider, bool $hasPickupPoint): ObjectProphecy
    {
        $method = $this->prophesize(ShippingMethodInterface::class);
        $method->hasPickupPointProvider()->willReturn($hasProvider);

        $shipment = $this->prophesize(ShipmentInterface::class);
        $shipment->getMethod()->willReturn($method->reveal());
        $shipment->hasPickupPoint()->willReturn($hasPickupPoint);

        return $shipment;
    }
}
