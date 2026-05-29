<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use InvalidArgumentException;
use function sprintf;

final class UnknownProviderException extends InvalidArgumentException implements ExceptionInterface
{
    /**
     * @param list<string> $availableCodes
     */
    public function __construct(string $code, array $availableCodes)
    {
        parent::__construct(sprintf(
            'No pickup point provider is registered with the code "%s". Available codes: %s',
            $code,
            [] === $availableCodes ? '(none)' : implode(', ', $availableCodes),
        ));
    }
}
