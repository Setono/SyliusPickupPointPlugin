<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Validator\Constraints;

use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\ShipmentInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class HasPickupPointSelectedValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$constraint instanceof HasPickupPointSelected) {
            throw new UnexpectedTypeException($constraint, HasPickupPointSelected::class);
        }

        if (!$value instanceof ShipmentInterface) {
            return;
        }

        $method = $value->getMethod();

        if (!$method instanceof PickupPointProviderAwareInterface) {
            return;
        }

        if (!$method->hasPickupPointProvider()) {
            return;
        }

        if (!$value->hasPickupPoint()) {
            $this->context
                ->buildViolation($constraint->pickupPointNotBlank)
                ->addViolation()
            ;
        }
    }
}
