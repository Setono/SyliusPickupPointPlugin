<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Exception;

use PhpSpec\ObjectBehavior;
use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;

final class NonUniqueProviderCodeExceptionSpec extends ObjectBehavior
{
    public function let(ProviderInterface $provider): void
    {
        $provider->getCode()->willReturn('gls');

        $this->beConstructedWith($provider);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(NonUniqueProviderCodeException::class);
    }

    public function it_is_a_invalid_argument_exception_exception(): void
    {
        $this->shouldHaveType(\InvalidArgumentException::class);
    }
}
