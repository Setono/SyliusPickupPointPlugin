<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use InvalidArgumentException;
use Safe\Exceptions\StringsException;
use function Safe\sprintf;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

final class NonUniqueProviderCodeException extends InvalidArgumentException implements PickupPointException
{
    /**
     * @throws StringsException
     */
    public function __construct(ProviderInterface $provider)
    {
        parent::__construct(sprintf('The code %s is not unique. Found in %s', $provider->getCode(), get_class($provider)));
    }
}
