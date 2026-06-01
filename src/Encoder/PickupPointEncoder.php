<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Encoder;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use function sprintf;

/**
 * Encodes a whole {@see PickupPoint} as base64url(JSON) so the chosen point can travel through the
 * page — into the AJAX response and back as the selected radio's value — and be persisted on submit
 * without ever calling the provider again. This is what lets the checkout fetch pickup points
 * asynchronously (the render never blocks on a provider) and still avoids re-resolving the point on
 * submit (which would block again and, for the faker provider, return a different random point).
 *
 * Mirrors {@see PickupPointIdentifierEncoder}, but carries the full point (name, address, …) — not just
 * its identifier — because nothing re-fetches the details server-side anymore.
 */
final class PickupPointEncoder implements PickupPointEncoderInterface
{
    public function encode(PickupPoint $pickupPoint): string
    {
        $json = json_encode($pickupPoint, \JSON_THROW_ON_ERROR);

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    public function decode(string $value): PickupPoint
    {
        $json = base64_decode(strtr($value, '-_', '+/'), true);
        if (false === $json) {
            throw new InvalidArgumentException(sprintf('The pickup point "%s" is not valid base64.', $value));
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new InvalidArgumentException(sprintf('The pickup point "%s" does not decode to an object.', $value));
        }

        /** @var array<string, mixed> $data */
        $data = $decoded;

        return PickupPoint::fromArray($data);
    }
}
