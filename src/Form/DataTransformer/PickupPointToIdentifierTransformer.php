<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use function Safe\sprintf;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class PickupPointToIdentifierTransformer implements DataTransformerInterface
{
    /** @var ServiceRegistryInterface */
    private $providerRegistry;

    public function __construct(ServiceRegistryInterface $providerRegistry)
    {
        $this->providerRegistry = $providerRegistry;
    }

    /**
     * @param mixed|PickupPoint $value
     */
    public function transform($value): ?PickupPointCode
    {
        if (null === $value) {
            return null;
        }

        $this->assertTransformationValueType($value, PickupPoint::class);

        return $value->getId();
    }

    /**
     * @param mixed $value
     */
    public function reverseTransform($value): ?PickupPoint
    {
        if (null === $value) {
            return null;
        }

        $pickupPointId = PickupPointCode::createFromString($value);

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($pickupPointId->getProviderPart());

        /** @var PickupPoint $pickupPoint */
        $pickupPoint = $provider->findPickupPoint($pickupPointId);

        $this->assertTransformationValueType($pickupPoint, PickupPoint::class);

        return $pickupPoint;
    }

    /**
     * @param mixed $value
     */
    private function assertTransformationValueType($value, string $expectedType): void
    {
        if (!$value instanceof $expectedType) {
            throw new TransformationFailedException(
                sprintf(
                    'Expected "%s", but got "%s"',
                    $expectedType,
                    is_object($value) ? get_class($value) : gettype($value)
                )
            );
        }
    }
}
