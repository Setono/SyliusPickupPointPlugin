<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use LogicException;
use function sprintf;

abstract class Provider implements ProviderInterface
{
    /**
     * The code this provider is registered under. Null until {@see setCode()} is called,
     * which the RegisterProvidersPass wires for every service tagged
     * "setono_sylius_pickup_point.provider". Read it through {@see getCode()}, never directly,
     * so an unregistered provider fails loudly instead of stamping an empty code.
     */
    private ?string $code = null;

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * Returns the code this provider was registered under, for stamping onto the pickup
     * points it returns.
     *
     * @throws LogicException if the code was never set — i.e. the provider was instantiated
     *                        without going through the compiler pass (not registered as a
     *                        service tagged "setono_sylius_pickup_point.provider")
     */
    protected function getCode(): string
    {
        if (null === $this->code) {
            throw new LogicException(sprintf(
                'The code has not been set on the pickup point provider "%s". It is set automatically when the provider is registered as a service tagged "setono_sylius_pickup_point.provider".',
                static::class,
            ));
        }

        return $this->code;
    }
}
