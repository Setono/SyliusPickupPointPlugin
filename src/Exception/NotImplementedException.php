<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use Exception;
use Throwable;

final class NotImplementedException extends Exception implements PickupPointException
{
    public function __construct($message = 'Not implemented', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
