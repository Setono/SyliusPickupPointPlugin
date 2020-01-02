<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use Safe\Exceptions\StringsException;
use function Safe\sprintf;
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
     * @param mixed $value
     *
     * @throws StringsException
     */
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        $this->assertTransformationValueType($value, PickupPointInterface::class);

        return $value->getFullId();
    }

    /**
     * @param mixed $value
     *
     * @throws StringsException
     */
    public function reverseTransform($value): ?PickupPointInterface
    {
        if (null === $value) {
            return null;
        }

        Assert::true(false !== mb_strpos($value, PickupPointInterface::TYPE_DELIMITER), 'PickupPoint identifier should contain delimiter.');
        [$pickupPointProvider, $pickupPointId] = explode(PickupPointInterface::TYPE_DELIMITER, $value);

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($pickupPointProvider);

        /** @var PickupPointInterface $pickupPoint */
        $pickupPoint = $provider->findOnePickupPointById($pickupPointId);

        $this->assertTransformationValueType($pickupPoint, PickupPointInterface::class);

        return $pickupPoint;
    }

    /**
     * @param mixed $value
     *
     * @throws TransformationFailedException
     * @throws StringsException
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
