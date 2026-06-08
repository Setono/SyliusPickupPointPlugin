<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Form\DataTransformer;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointEncoderInterface;
use function sprintf;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Bridges the {@see PickupPoint} model value and the opaque token carried by the hidden input.
 *
 * Both directions go through {@see PickupPointEncoderInterface} only — no provider lookup — so a slow
 * or unavailable carrier API never blocks the submit, and the persisted point is exactly the one the
 * shopper picked (the AJAX response carried the same token).
 */
final readonly class PickupPointTransformer implements DataTransformerInterface
{
    public function __construct(private PickupPointEncoderInterface $encoder)
    {
    }

    public function transform(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PickupPoint) {
            throw new TransformationFailedException(
                sprintf('Expected "%s", but got "%s"', PickupPoint::class, get_debug_type($value)),
            );
        }

        return $this->encoder->encode($value);
    }

    public function reverseTransform(mixed $value): ?PickupPoint
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException(sprintf('Expected string, got "%s"', get_debug_type($value)));
        }

        try {
            return $this->encoder->decode($value);
        } catch (InvalidArgumentException $e) {
            throw new TransformationFailedException($e->getMessage(), 0, $e);
        }
    }
}
