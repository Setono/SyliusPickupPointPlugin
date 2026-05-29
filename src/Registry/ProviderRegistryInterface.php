<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Registry;

use Setono\SyliusPickupPointPlugin\Exception\UnknownProviderException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

/**
 * @extends \IteratorAggregate<string, ProviderInterface>
 */
interface ProviderRegistryInterface extends \IteratorAggregate
{
    /**
     * Registers a provider under the given code. The name is the human-readable
     * carrier name shown to merchants. Wired by the RegisterProvidersPass.
     */
    public function add(ProviderInterface $provider, string $code, string $name): void;

    public function has(string $code): bool;

    /**
     * @throws UnknownProviderException if no provider is registered with the given code
     */
    public function get(string $code): ProviderInterface;

    /**
     * @return array<string, ProviderInterface>
     */
    public function all(): array;

    /**
     * @return array<string, string> a map of provider code => human-readable name
     */
    public function names(): array;
}
