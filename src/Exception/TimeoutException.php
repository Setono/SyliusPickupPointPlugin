<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use RuntimeException;
use Throwable;

/**
 * This is a special exception only thrown when a timeout occurs when trying to fetch pickup point(s) from external providers
 */
final class TimeoutException extends RuntimeException implements ExceptionInterface
{
    public function __construct(Throwable $previous = null)
    {
        parent::__construct('This is a special exception only thrown when a timeout occours when trying to fetch pickup point(s) from external providers', 0, $previous);
    }
}
