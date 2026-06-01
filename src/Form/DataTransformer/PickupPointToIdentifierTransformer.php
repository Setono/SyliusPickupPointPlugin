<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistryInterface;
use function sprintf;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final readonly class PickupPointToIdentifierTransformer implements DataTransformerInterface
{
    public function __construct(
        private ProviderRegistryInterface $providerRegistry,
        private PickupPointIdentifierEncoderInterface $encoder,
    ) {
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

        $identifier = PickupPointIdentifier::fromPickupPoint($value);

        return null === $identifier ? null : $this->encoder->encode($identifier);
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

        try {
            // Both a malformed token (decode) and an unknown provider code (UnknownProviderException,
            // itself an InvalidArgumentException) become a clean transformation failure rather than a 500.
            $identifier = $this->encoder->decode($value);
            $provider = $this->providerRegistry->get($identifier->provider);
        } catch (InvalidArgumentException $e) {
            throw new TransformationFailedException($e->getMessage(), 0, $e);
        }

        return $provider->findPickupPoint($identifier->id, $identifier->metadata);
    }
}
