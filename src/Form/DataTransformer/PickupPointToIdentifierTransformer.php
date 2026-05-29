<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use function sprintf;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class PickupPointToIdentifierTransformer implements DataTransformerInterface
{
    public function __construct(private ProviderRegistryInterface $providerRegistry)
    {
    }

    /**
     * @param mixed|PickupPoint $value
     */
    public function transform($value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PickupPoint) {
            throw new TransformationFailedException(
                sprintf('Expected "%s", but got "%s"', PickupPoint::class, get_debug_type($value)),
            );
        }

        if (null === $value->provider || null === $value->id || null === $value->country) {
            return null;
        }

        return sprintf('%s---%s---%s', $value->provider, $value->id, $value->country);
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

        $parts = explode('---', $value);
        if (3 !== count($parts)) {
            throw new TransformationFailedException(sprintf('Expected "provider---id---country", got "%s"', $value));
        }

        [$providerCode, $id, $country] = $parts;

        return $this->providerRegistry->get($providerCode)->findPickupPoint($id, ['country' => $country]);
    }
}
