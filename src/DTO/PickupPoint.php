<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DTO;

final class PickupPoint implements \JsonSerializable
{
    public ?string $provider = null;

    public ?string $id = null;

    public ?string $name = null;

    public ?string $address = null;

    public ?string $zipCode = null;

    public ?string $city = null;

    public ?string $country = null;

    public ?string $latitude = null;

    public ?string $longitude = null;

    /**
     * Open, provider-defined data carried inside the pickup point identifier so a custom provider
     * can round-trip whatever extra context its
     * {@see \Setono\SyliusPickupPointPlugin\Provider\ProviderInterface::findPickupPoint()} needs
     * to re-resolve the point by its id (a region, a warehouse, a token, …). The well-known
     * {@see self::$country} is folded in automatically by {@see PickupPointIdentifier::fromPickupPoint()},
     * so it need not be repeated here.
     *
     * @var array<string, mixed>
     */
    public array $metadata = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $pickupPoint = new self();
        $pickupPoint->provider = self::scalarOrNull($data['provider'] ?? null);
        $pickupPoint->id = self::scalarOrNull($data['id'] ?? null);
        $pickupPoint->name = self::scalarOrNull($data['name'] ?? null);
        $pickupPoint->address = self::scalarOrNull($data['address'] ?? null);
        $pickupPoint->zipCode = self::scalarOrNull($data['zipCode'] ?? null);
        $pickupPoint->city = self::scalarOrNull($data['city'] ?? null);
        $pickupPoint->country = self::scalarOrNull($data['country'] ?? null);
        $pickupPoint->latitude = self::scalarOrNull($data['latitude'] ?? null);
        $pickupPoint->longitude = self::scalarOrNull($data['longitude'] ?? null);
        $pickupPoint->metadata = self::stringKeyedArray($data['metadata'] ?? null);

        return $pickupPoint;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'provider' => $this->provider,
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'zipCode' => $this->zipCode,
            'city' => $this->city,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Returns the value as a string, accepting strings as-is and stringifying int/float.
     *
     * Some fields (id, zip code, coordinates) can legitimately be numeric: when the DTO is
     * rehydrated from the Doctrine JSON column (see {@see \Setono\SyliusPickupPointPlugin\Model\PickupPointAwareTrait}),
     * JSON numbers decode as int/float, so coercing them here prevents the value from being
     * silently dropped. Booleans are intentionally not accepted — `(string) false` is '' and
     * a bool is never a valid field value.
     */
    private static function scalarOrNull(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
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
