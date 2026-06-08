<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Encoder;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\DTO\PickupPointIdentifier;
use function sprintf;

/**
 * Encodes the identifier as base64url(JSON) so it can hold an arbitrary metadata map while
 * remaining safe to use as a form value / query string. The token is opaque on purpose.
 */
final class PickupPointIdentifierEncoder implements PickupPointIdentifierEncoderInterface
{
    public function encode(PickupPointIdentifier $identifier): string
    {
        $json = json_encode($identifier, \JSON_THROW_ON_ERROR);

        // base64url (RFC 4648 §5): base64-encode, then make it URL- and form-safe by swapping the two
        // non-safe characters (`+` -> `-`, `/` -> `_`) and dropping the `=` padding. `decode()` reverses it.
        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    public function decode(string $value): PickupPointIdentifier
    {
        $json = base64_decode(strtr($value, '-_', '+/'), true);
        if (false === $json) {
            throw new InvalidArgumentException(sprintf('The pickup point identifier "%s" is not valid base64.', $value));
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new InvalidArgumentException(sprintf('The pickup point identifier "%s" does not decode to an object.', $value));
        }

        return PickupPointIdentifier::fromArray($decoded);
    }
}
