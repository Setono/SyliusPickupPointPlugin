<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use function sprintf;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class PickupPointToIdentifierTransformer implements DataTransformerInterface
{
    private ServiceRegistryInterface $providerRegistry;

    public function __construct(ServiceRegistryInterface $providerRegistry)
    {
        $this->providerRegistry = $providerRegistry;
    }

    /**
     * @param mixed|PickupPointInterface $value
     */
    public function transform($value): ?PickupPointCode
    {
        if (null === $value) {
            return null;
        }

        $this->assertTransformationValueType($value, PickupPointInterface::class);

        return $value->getCode();
    }

    /**
     * @param mixed $value
     */
    public function reverseTransform($value): ?PickupPointInterface
    {
        if (null === $value) {
            return null;
        }

        $pickupPointId = PickupPointCode::createFromString($value);

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($pickupPointId->getProviderPart());

        /** @var PickupPointInterface $pickupPoint */
        $pickupPoint = $provider->findPickupPoint($pickupPointId);

        $this->assertTransformationValueType($pickupPoint, PickupPointInterface::class);

        return $pickupPoint;
    }

    /**
     * @template ExpectedType of object
     *
     * @param mixed $value
     * @param class-string<ExpectedType> $expectedType
     * @psalm-assert ExpectedType $value
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
