<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Exception;

use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;
use PhpSpec\ObjectBehavior;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Webmozart\Assert\Assert;

final class NonUniqueProviderCodeExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedWith('The code for the provider is not unique', 0, null);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(NonUniqueProviderCodeException::class);
    }

    function it_is_a_invalid_argument_exception_exception(): void
    {
        $this->shouldHaveType(\InvalidArgumentException::class);
    }

    function it_creates_message(ProviderInterface $provider): void
    {
        $provider->getCode()->willReturn('gls');

        $e = $this->create($provider);

        $e->shouldBeAnInstanceOf(NonUniqueProviderCodeException::class);
    }
}
