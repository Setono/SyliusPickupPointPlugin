<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use RuntimeException;

/**
 * This is a special exception only thrown when a timeout occours when trying to fetch pickup point(s) from external providers
 */
final class TimeoutException extends RuntimeException implements ExceptionInterface
{
    public function __construct()
    {
        parent::__construct('This is a special exception only thrown when a timeout occours when trying to fetch pickup point(s) from external providers');
    }
}
