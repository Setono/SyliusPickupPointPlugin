<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Webmozart\Assert\Assert;

final class PickupPointToIdentifierTransformer implements DataTransformerInterface
{
    /** @var ServiceRegistryInterface */
    private $providerRegistry;

    public function __construct(ServiceRegistryInterface $providerRegistry)
    {
        $this->providerRegistry = $providerRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @param PickupPointInterface|null $value
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        $this->assertTransformationValueType($value, PickupPointInterface::class);

        return $value->getFullId();
    }

    /**
     * {@inheritdoc}
     *
     * @return PickupPointInterface|null
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        Assert::true(false !== strpos($value, PickupPointInterface::TYPE_DELIMITER), 'PickupPoint identifier should contain delimiter.');
        [$pickupPointProvider, $pickupPointId] = explode(PickupPointInterface::TYPE_DELIMITER, $value);

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($pickupPointProvider);

        /** @var PickupPointInterface $pickupPoint */
        $pickupPoint = $provider->findOnePickupPointById($pickupPointId);

        $this->assertTransformationValueType($pickupPoint, PickupPointInterface::class);

        return $pickupPoint;
    }

    /**
     * @throws TransformationFailedException
     */
    private function assertTransformationValueType($value, string $expectedType): void
    {
        if (!($value instanceof $expectedType)) {
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
