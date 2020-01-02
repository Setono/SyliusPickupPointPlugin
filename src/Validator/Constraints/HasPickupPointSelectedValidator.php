<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Validator\Constraints;

use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\ShipmentInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class HasPickupPointSelectedValidator extends ConstraintValidator
{
    /**
     * @param ShipmentInterface|mixed $shipment
     * @param HasPickupPointSelected|Constraint $constraint
     */
    public function validate($shipment, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, HasPickupPointSelected::class);

        Assert::isInstanceOf($shipment, ShipmentInterface::class);

        /** @var PickupPointProviderAwareInterface $method */
        $method = $shipment->getMethod();

        if (!$method->hasPickupPointProvider()) {
            return;
        }

        if (!$shipment->hasPickupPointId()) {
            $this->context
                ->buildViolation($constraint->pickupPointNotBlank)
                ->addViolation()
            ;

            return;
        }
    }
}
