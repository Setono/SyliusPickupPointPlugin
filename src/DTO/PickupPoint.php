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
        $pickupPoint->id = self::stringOrNull($data['id'] ?? null);
        $pickupPoint->name = self::stringOrNull($data['name'] ?? null);
        $pickupPoint->address = self::stringOrNull($data['address'] ?? null);
        $pickupPoint->zipCode = self::stringOrNull($data['zipCode'] ?? null);
        $pickupPoint->city = self::stringOrNull($data['city'] ?? null);
        $pickupPoint->country = self::stringOrNull($data['country'] ?? null);
        $pickupPoint->latitude = self::stringOrNull($data['latitude'] ?? null);
        $pickupPoint->longitude = self::stringOrNull($data['longitude'] ?? null);

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
}
