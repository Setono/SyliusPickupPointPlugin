<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Provider;

use LogicException;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\DTO\Address;
use Setono\SyliusPickupPointPlugin\DTO\PickupPoint;
use Setono\SyliusPickupPointPlugin\Provider\Provider;

final class ProviderTest extends TestCase
{
    public function testItThrowsWhenTheCodeIsReadBeforeItIsSet(): void
    {
        $provider = $this->createProvider();

        $this->expectException(LogicException::class);

        $provider->exposeCode();
    }

    public function testItReturnsTheCodeAfterItIsSet(): void
    {
        $provider = $this->createProvider();

        $provider->setCode('x');

        self::assertSame('x', $provider->exposeCode());
    }

    private function createProvider(): ProviderTestStub
    {
        return new ProviderTestStub();
    }
}

/**
 * Concrete stub used to exercise the abstract {@see Provider} base. The interface
 * methods are trivial; {@see exposeCode()} surfaces the protected getCode() so the
 * code-resolution behaviour can be asserted.
 */
final class ProviderTestStub extends Provider
{
    public function findPickupPoints(Address $address): array
    {
        return [];
    }

    public function findPickupPoint(string $id, array $metadata = []): ?PickupPoint
    {
        return null;
    }

    public function exposeCode(): string
    {
        return $this->getCode();
    }
}
