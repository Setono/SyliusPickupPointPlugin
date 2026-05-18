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
         * The label shown to merchants in the admin shipping-method form when
         * picking the provider for a shipping method. Translated through the
         * standard Symfony translator, so this is typically a translation key
         * (e.g. `"setono_sylius_pickup_point.provider.faker"`) rather than a
         * literal user-facing string.
         */
        public string $name,
    ) {
    }
}
