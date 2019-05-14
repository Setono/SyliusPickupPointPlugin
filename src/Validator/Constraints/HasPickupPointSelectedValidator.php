<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Validator\Constraints;

use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointIdAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class HasPickupPointSelectedValidator extends ConstraintValidator
{
    /**
     * @var ProviderManagerInterface
     */
    private $providerManager;

    /**
     * @param ProviderManagerInterface $providerManager
     */
    public function __construct(ProviderManagerInterface $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    public function validate($shipment, Constraint $constraint): void
    {
        /** @var $constraint HasPickupPointSelected */
        Assert::isInstanceOf($constraint, HasPickupPointSelected::class);

        /** @var $shipment PickupPointIdAwareInterface|ShipmentInterface */
        Assert::isInstanceOf($shipment, PickupPointIdAwareInterface::class);
        Assert::isInstanceOf($shipment, ShipmentInterface::class);

        /** @var PickupPointProviderAwareInterface $method */
        $method = $shipment->getMethod();

        if (!$method->hasPickupPointProvider()) {
            return;
        }

        if (!$shipment->hasPickupPointId()) {
            $this->context->buildViolation($constraint->pickupPointNotBlank)
                ->addViolation()
            ;

            return;
        }

        /** @var ProviderInterface $provider */
        $provider = $this->providerManager->findByClassName($method->getPickupPointProvider());

        if (null === $provider->getPickupPointById($shipment->getPickupPointId())) {
            $this->context->buildViolation($constraint->pickupPointNotExists)
                ->addViolation()
            ;

            return;
        }
    }
}
