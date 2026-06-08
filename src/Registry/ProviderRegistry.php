<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Registry;

use Setono\SyliusPickupPointPlugin\Exception\UnknownProviderException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

final class ProviderRegistry implements ProviderRegistryInterface
{
    /**
     * Map of provider code => provider instance. This is the registry's single source of
     * truth and is populated once at container build time: RegisterProvidersPass resolves
     * each tagged provider's code (from its #[AsProvider] attribute or its service tag) and
     * wires one add() call per provider. The same code is also handed to the provider via
     * setCode(), so the instance and the registry agree on it.
     *
     * @var array<string, ProviderInterface>
     */
    private array $providers = [];

    /**
     * Map of provider code => human-readable carrier name (e.g. 'gls' => 'GLS'). Used as the
     * label shown to merchants when picking a provider in the admin shipping-method form.
     * Kept in lock-step with {@see $providers} — both are filled by the same add() call.
     *
     * @var array<string, string>
     */
    private array $names = [];

    public function add(ProviderInterface $provider, string $code, string $name): void
    {
        $this->providers[$code] = $provider;
        $this->names[$code] = $name;
    }

    public function has(string $code): bool
    {
        return isset($this->providers[$code]);
    }

    public function get(string $code): ProviderInterface
    {
        return $this->providers[$code] ?? throw new UnknownProviderException($code, array_keys($this->providers));
    }

    public function all(): array
    {
        return $this->providers;
    }

    public function names(): array
    {
        return $this->names;
    }

    /**
     * Allows iterating the registry directly, e.g. `foreach ($registry as $code => $provider)`.
     *
     * @return \ArrayIterator<string, ProviderInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->providers);
    }
}
