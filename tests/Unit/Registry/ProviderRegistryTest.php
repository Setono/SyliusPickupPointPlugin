<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Registry;

use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Exception\UnknownProviderException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Registry\ProviderRegistry;

final class ProviderRegistryTest extends TestCase
{
    private ProviderRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new ProviderRegistry();
    }

    public function testItStartsEmpty(): void
    {
        self::assertFalse($this->registry->has('gls'));
        self::assertSame([], $this->registry->all());
        self::assertSame([], $this->registry->names());
    }

    public function testItRegistersAndRetrievesAProvider(): void
    {
        $provider = $this->createMock(ProviderInterface::class);

        $this->registry->add($provider, 'gls', 'GLS');

        self::assertTrue($this->registry->has('gls'));
        self::assertSame($provider, $this->registry->get('gls'));
    }

    public function testHasReturnsFalseForUnknownCode(): void
    {
        $this->registry->add($this->createMock(ProviderInterface::class), 'gls', 'GLS');

        self::assertFalse($this->registry->has('dao'));
    }

    public function testAllReturnsTheCodeToProviderMap(): void
    {
        $gls = $this->createMock(ProviderInterface::class);
        $dao = $this->createMock(ProviderInterface::class);

        $this->registry->add($gls, 'gls', 'GLS');
        $this->registry->add($dao, 'dao', 'DAO');

        self::assertSame(['gls' => $gls, 'dao' => $dao], $this->registry->all());
    }

    public function testNamesReturnsTheCodeToNameMap(): void
    {
        $this->registry->add($this->createMock(ProviderInterface::class), 'gls', 'GLS');
        $this->registry->add($this->createMock(ProviderInterface::class), 'dao', 'DAO');

        self::assertSame(['gls' => 'GLS', 'dao' => 'DAO'], $this->registry->names());
    }

    public function testGetThrowsForUnknownCode(): void
    {
        $this->registry->add($this->createMock(ProviderInterface::class), 'gls', 'GLS');

        $this->expectException(UnknownProviderException::class);
        $this->expectExceptionMessage('No pickup point provider is registered with the code "dao". Available codes: gls');

        $this->registry->get('dao');
    }

    public function testGetThrowsWithNoneWhenRegistryIsEmpty(): void
    {
        $this->expectException(UnknownProviderException::class);
        $this->expectExceptionMessage('Available codes: (none)');

        $this->registry->get('gls');
    }

    public function testRegisteringTheSameCodeTwiceOverwritesThePreviousEntry(): void
    {
        $first = $this->createMock(ProviderInterface::class);
        $second = $this->createMock(ProviderInterface::class);

        $this->registry->add($first, 'gls', 'GLS');
        $this->registry->add($second, 'gls', 'GLS (override)');

        self::assertSame($second, $this->registry->get('gls'));
        self::assertSame(['gls' => $second], $this->registry->all());
        self::assertSame(['gls' => 'GLS (override)'], $this->registry->names());
    }

    public function testItIsIterable(): void
    {
        $gls = $this->createMock(ProviderInterface::class);
        $dao = $this->createMock(ProviderInterface::class);

        $this->registry->add($gls, 'gls', 'GLS');
        $this->registry->add($dao, 'dao', 'DAO');

        self::assertSame(['gls' => $gls, 'dao' => $dao], iterator_to_array($this->registry));
    }
}
