<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Tests\Unit\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Setono\SyliusPickupPointPlugin\Exception\ExceptionInterface;
use Setono\SyliusPickupPointPlugin\Exception\UnknownProviderException;
use Throwable;

final class UnknownProviderExceptionTest extends TestCase
{
    public function testItIsAThrowableImplementingTheExceptionInterface(): void
    {
        $exception = new UnknownProviderException('dao', []);

        self::assertInstanceOf(Throwable::class, $exception);
        self::assertInstanceOf(ExceptionInterface::class, $exception);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    public function testItListsTheAvailableCodesInTheMessage(): void
    {
        $exception = new UnknownProviderException('foo', ['dao', 'gls', 'post_nord']);

        self::assertSame(
            'No pickup point provider is registered with the code "foo". Available codes: dao, gls, post_nord',
            $exception->getMessage(),
        );
    }

    public function testItRendersNoneWhenThereAreNoAvailableCodes(): void
    {
        $exception = new UnknownProviderException('foo', []);

        self::assertSame(
            'No pickup point provider is registered with the code "foo". Available codes: (none)',
            $exception->getMessage(),
        );
    }
}
