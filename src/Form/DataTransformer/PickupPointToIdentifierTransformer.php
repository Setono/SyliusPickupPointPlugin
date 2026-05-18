<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use function sprintf;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class PickupPointToIdentifierTransformer implements DataTransformerInterface
{
    public function __construct(private ServiceRegistryInterface $providerRegistry)
    {
    }

    /**
     * @param mixed|PickupPoint $value
     */
    public function transform($value): ?PickupPointCode
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PickupPoint) {
            throw new TransformationFailedException(
                sprintf('Expected "%s", but got "%s"', PickupPoint::class, get_debug_type($value)),
            );
        }

        return $value->code;
    }

    /**
     * @param mixed $value
     */
    public function reverseTransform($value): ?PickupPoint
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException(sprintf('Expected string, got "%s"', get_debug_type($value)));
        }

        $pickupPointId = PickupPointCode::createFromString($value);

        /** @var ProviderInterface $provider */
        $provider = $this->providerRegistry->get($pickupPointId->getProviderPart());

        return $provider->findPickupPoint($pickupPointId);
    }
}
