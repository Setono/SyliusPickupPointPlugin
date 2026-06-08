<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use InvalidArgumentException;
use function sprintf;

final class NonUniqueProviderCodeException extends InvalidArgumentException implements ExceptionInterface
{
    public function __construct(string $code)
    {
        parent::__construct(sprintf('More than one pickup point provider is registered with the code "%s". Provider codes must be unique.', $code));
    }
}
