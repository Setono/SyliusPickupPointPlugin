<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use InvalidArgumentException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use function sprintf;

final class NonUniqueProviderCodeException extends InvalidArgumentException implements ExceptionInterface
{
    public function __construct(ProviderInterface $provider)
    {
        parent::__construct(sprintf('The code %s is not unique. Found in %s', $provider->getCode(), $provider::class));
    }
}
