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
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $pickupPoint = new self();
        $pickupPoint->provider = self::stringOrNull($data['provider'] ?? null);
        $pickupPoint->id = self::scalarStringOrNull($data['id'] ?? null);
        $pickupPoint->name = self::stringOrNull($data['name'] ?? null);
        $pickupPoint->address = self::stringOrNull($data['address'] ?? null);
        $pickupPoint->zipCode = self::stringOrNull($data['zipCode'] ?? null);
        $pickupPoint->city = self::stringOrNull($data['city'] ?? null);
        $pickupPoint->country = self::stringOrNull($data['country'] ?? null);
        $pickupPoint->latitude = self::scalarStringOrNull($data['latitude'] ?? null);
        $pickupPoint->longitude = self::scalarStringOrNull($data['longitude'] ?? null);

        return $pickupPoint;
    }

    /**
     * @return array<string, string|null>
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
        ];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) ? $value : null;
    }

    /**
     * Like {@see stringOrNull()} but also accepts int/float and stringifies them.
     *
     * The id and the coordinates can legitimately be numeric: when the DTO is rehydrated
     * from the Doctrine JSON column (see {@see \Setono\SyliusPickupPointPlugin\Model\PickupPointAwareTrait}),
     * JSON numbers decode as int/float, so coercing them here prevents the value from being
     * silently dropped. Booleans are intentionally not accepted — `(string) false` is '' and
     * a bool is never a valid id or coordinate.
     */
    private static function scalarStringOrNull(mixed $value): ?string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }
}
