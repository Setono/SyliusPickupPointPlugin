<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Encoder;

use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;

/**
 * Owns the identifier's transport encoding: it frames the JSON shape defined by
 * {@see PickupPointIdentifier} as the opaque, URL/form-safe string carried between the shop
 * (form value / AJAX) and PHP.
 */
interface PickupPointIdentifierEncoderInterface
{
    public function encode(PickupPointIdentifier $identifier): string;

    /**
     * @throws \InvalidArgumentException if $value is not a valid encoded identifier
     */
    public function decode(string $value): PickupPointIdentifier;
}
