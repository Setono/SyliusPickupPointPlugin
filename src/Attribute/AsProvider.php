<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class AsProvider
{
    public function __construct(
        /**
         * A unique machine identifier for the provider — used as the key in the
         * provider registry, as the value submitted by the shipping-method
         * choice and as the `provider` part of the pickup-point wire-format
         * string. Examples: `"faker"`, `"gls"`, `"post_nord"`.
         */
        public string $code,

        /**
         * The human-readable company name of the provider as it appears to
         * merchants in the admin shipping-method form when they pick the
         * provider for a shipping method — e.g. `"GLS"`, `"PostNord"`,
         * `"DAO"`. This is the carrier's brand name, not a translation key.
         */
        public string $name,
    ) {
    }
}
