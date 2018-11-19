<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Exception;

use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Throwable;

class NonUniqueProviderCodeException extends \InvalidArgumentException
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = 'The code for the provider is not unique', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param ProviderInterface $provider
     *
     * @return NonUniqueProviderCodeException
     */
    public static function create(ProviderInterface $provider): self
    {
        return new self("The code '" . $provider->getCode() . "' is not unique. Found in " . get_class($provider));
    }
}
