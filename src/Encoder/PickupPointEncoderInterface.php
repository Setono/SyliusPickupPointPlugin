<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Encoder;

use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;

interface PickupPointEncoderInterface
{
    /**
     * Encodes a full pickup point into an opaque, transport-safe token.
     *
     * @throws \JsonException if the point cannot be encoded (e.g. it holds a non-UTF-8 string)
     */
    public function encode(PickupPoint $pickupPoint): string;

    /**
     * Reverses {@see encode()}.
     *
     * @throws \InvalidArgumentException if the token is malformed
     */
    public function decode(string $value): PickupPoint;
}
