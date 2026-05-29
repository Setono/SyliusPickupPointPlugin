<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Serializer\Normalizer;

use LogicException;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;
use Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoderInterface;
use function sprintf;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Augments the default normalization of a {@see PickupPoint} with an encoded `identifier` token.
 *
 * Rather than building the base representation itself, it delegates back to the serializer (which,
 * for this JsonSerializable DTO, lands on the built-in JsonSerializableNormalizer →
 * {@see PickupPoint::jsonSerialize()}) and only adds the token. The token needs the
 * {@see PickupPointIdentifierEncoderInterface} service, which a value object cannot hold, so it is
 * added on the serializer path only; persistence calls jsonSerialize() directly and never carries it.
 */
final class PickupPointNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * Once we have delegated, this context flag makes {@see supportsNormalization()} step aside so
     * the next normalizer in the chain produces the base representation — preventing infinite recursion.
     */
    private const ALREADY_CALLED = 'setono_sylius_pickup_point_normalizer_already_called';

    public function __construct(
        private readonly PickupPointIdentifierEncoderInterface $encoder,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<array-key, mixed>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof PickupPoint) {
            throw new InvalidArgumentException(sprintf('The data must be an instance of "%s".', PickupPoint::class));
        }

        $context[self::ALREADY_CALLED] = true;

        $normalized = $this->normalizer->normalize($data, $format, $context);
        if (!is_array($normalized)) {
            throw new LogicException(sprintf(
                'Expected the normalized pickup point to be an array, but got "%s".',
                get_debug_type($normalized),
            ));
        }

        $identifier = PickupPointIdentifier::fromPickupPoint($data);
        $normalized['identifier'] = null === $identifier ? null : $this->encoder->encode($identifier);

        return $normalized;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (true === ($context[self::ALREADY_CALLED] ?? false)) {
            return false;
        }

        return $data instanceof PickupPoint;
    }

    /**
     * @return array<class-string|'*'|'object'|string, bool|null>
     */
    public function getSupportedTypes(?string $format): array
    {
        // Not cacheable: support depends on the ALREADY_CALLED context flag, so supportsNormalization()
        // must be consulted on every call for the recursion guard to work.
        return [PickupPoint::class => false];
    }
}
