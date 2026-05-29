<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DTO;

/**
 * The decoded form of a pickup point identifier: the provider it belongs to, the
 * provider-local id, and the open metadata needed to re-resolve it (see
 * {@see PickupPoint::$metadata}). Implements {@see \JsonSerializable} so it is the single
 * source of the identifier's wire shape; the bytes on the wire are produced from it by
 * {@see \Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoderInterface}.
 */
final readonly class PickupPointIdentifier implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $provider,
        public string $id,
        public array $metadata = [],
    ) {
    }

    /**
     * Builds an identifier from a pickup point, or null when the point lacks the
     * provider/id needed to identify it.
     *
     * The well-known {@see PickupPoint::$country} is a first-class property on the point but
     * travels in the identifier's metadata, because that is what
     * {@see \Setono\SyliusPickupPointPlugin\Provider\ProviderInterface::findPickupPoint()}
     * consumes to re-resolve the point by id.
     */
    public static function fromPickupPoint(PickupPoint $pickupPoint): ?self
    {
        if (null === $pickupPoint->provider || null === $pickupPoint->id) {
            return null;
        }

        $metadata = $pickupPoint->metadata;
        if (null !== $pickupPoint->country) {
            // "+" keeps the left operand on a key collision, so the first-class country wins over
            // any stale metadata['country'] a consumer set. Do not flip the operands.
            $metadata = ['country' => $pickupPoint->country] + $metadata;
        }

        return new self($pickupPoint->provider, $pickupPoint->id, $metadata);
    }

    /**
     * Rebuilds an identifier from its decoded array form — the inverse of {@see jsonSerialize()}.
     *
     * @param array<array-key, mixed> $data
     *
     * @throws \InvalidArgumentException if $data lacks a string "provider" and/or "id"
     */
    public static function fromArray(array $data): self
    {
        $provider = $data['provider'] ?? null;
        $id = $data['id'] ?? null;
        if (!is_string($provider) || !is_string($id)) {
            throw new \InvalidArgumentException('A pickup point identifier requires a string "provider" and "id".');
        }

        return new self($provider, $id, self::stringKeyedArray($data['metadata'] ?? null));
    }

    /**
     * @return array{provider: string, id: string, metadata: array<string, mixed>}
     */
    public function jsonSerialize(): array
    {
        return [
            'provider' => $this->provider,
            'id' => $this->id,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function stringKeyedArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $key => $item) {
            $result[(string) $key] = $item;
        }

        return $result;
    }
}
