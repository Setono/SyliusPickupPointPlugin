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
        $pickupPoint->provider = self::scalarOrNull($data['provider'] ?? null);
        $pickupPoint->id = self::scalarOrNull($data['id'] ?? null);
        $pickupPoint->name = self::scalarOrNull($data['name'] ?? null);
        $pickupPoint->address = self::scalarOrNull($data['address'] ?? null);
        $pickupPoint->zipCode = self::scalarOrNull($data['zipCode'] ?? null);
        $pickupPoint->city = self::scalarOrNull($data['city'] ?? null);
        $pickupPoint->country = self::scalarOrNull($data['country'] ?? null);
        $pickupPoint->latitude = self::scalarOrNull($data['latitude'] ?? null);
        $pickupPoint->longitude = self::scalarOrNull($data['longitude'] ?? null);

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
}
