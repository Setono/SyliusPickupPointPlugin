<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DTO;

use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

final class PickupPoint
{
    public ?PickupPointCode $code = null;

    #[Groups(['Detailed', 'Autocomplete'])]
    public ?string $name = null;

    public ?string $address = null;

    public ?string $zipCode = null;

    public ?string $city = null;

    public ?string $country = null;

    #[Groups(['Detailed', 'Autocomplete'])]
    public ?float $latitude = null;

    #[Groups(['Detailed', 'Autocomplete'])]
    public ?float $longitude = null;

    #[Groups(['Detailed', 'Autocomplete'])]
    #[SerializedName('code')]
    public function getCodeValue(): ?string
    {
        return $this->code?->getValue();
    }

    #[Groups(['Detailed', 'Autocomplete'])]
    #[SerializedName('full_address')]
    public function getFullAddress(): string
    {
        return sprintf('%s, %s %s', (string) $this->address, (string) $this->zipCode, (string) $this->city);
    }
}
