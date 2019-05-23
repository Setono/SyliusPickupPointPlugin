<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Validator\Constraints;

use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class HasPickupPointSelectedValidator extends ConstraintValidator
{
    public function validate($shipment, Constraint $constraint): void
    {
        /** @var $constraint HasPickupPointSelected */
        Assert::isInstanceOf($constraint, HasPickupPointSelected::class);

        /** @var $shipment PickupPointAwareInterface|ShipmentInterface */
        Assert::isInstanceOf($shipment, PickupPointAwareInterface::class);
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
