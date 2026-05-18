<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

final class NonUniqueProviderCodeExceptionTest extends TestCase
{
    use ProphecyTrait;

    public function testItIsAnInvalidArgumentException(): void
    {
        $provider = $this->prophesize(ProviderInterface::class);
        $provider->getCode()->willReturn('gls');

        $exception = new NonUniqueProviderCodeException($provider->reveal());

        self::assertInstanceOf(NonUniqueProviderCodeException::class, $exception);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
