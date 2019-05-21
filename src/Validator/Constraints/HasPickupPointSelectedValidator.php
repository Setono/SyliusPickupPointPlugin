<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Validator\Constraints;

use Setono\SyliusPickupPointPlugin\Model\PickupPointAwareInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPointProviderAwareInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\ShipmentInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class HasPickupPointSelectedValidator extends ConstraintValidator
{
    /**
     * @var ServiceRegistryInterface
     */
    private $providerRegistry;

    public function __construct(ServiceRegistryInterface $providerRegistry)
    {
        $this->providerRegistry = $providerRegistry;
    }

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

        if (!$this->providerRegistry->has($method->getPickupPointProvider())) {
            return;
        }

        if (!$shipment->hasPickupPointId()) {
            $this->context
                ->buildViolation($constraint->pickupPointNotBlank)
                ->addViolation()
            ;

            return;
        }

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($method->getPickupPointProvider());

        if (null === $provider->getPickupPointById($shipment->getPickupPointId())) {
            $this->context
                ->buildViolation($constraint->pickupPointNotExists)
                ->addViolation()
            ;

            return;
        }
    }
}
