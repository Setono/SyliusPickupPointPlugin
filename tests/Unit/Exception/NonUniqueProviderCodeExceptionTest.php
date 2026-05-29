<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;

final class NonUniqueProviderCodeExceptionTest extends TestCase
{
    public function testItIsAnInvalidArgumentException(): void
    {
        $exception = new NonUniqueProviderCodeException('gls');

        self::assertInstanceOf(NonUniqueProviderCodeException::class, $exception);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }
}
