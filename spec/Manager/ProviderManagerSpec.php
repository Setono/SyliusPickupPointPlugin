<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Manager;

use Setono\SyliusPickupPointPlugin\Manager\ProviderManager;
use PhpSpec\ObjectBehavior;
use Setono\SyliusPickupPointPlugin\Manager\ProviderManagerInterface;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Setono\SyliusPickupPointPlugin\Exception\NonUniqueProviderCodeException;

final class ProviderManagerSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(ProviderManager::class);
    }

    function it_implements_an_provider_manager_interface(): void
    {
        $this->shouldImplement(ProviderManagerInterface::class);
    }

    function it_adds_a_provider(ProviderInterface $glsProvider): void
    {
        $glsProvider->getCode()->willReturn('gls');

        $this->addProvider($glsProvider);

        $this->findByCode('gls')->shouldReturn($glsProvider);
    }

    function it_throws_an_exception_if_provider_code_is_not_unique(ProviderInterface $glsProvider): void
    {
        $glsProvider->getCode()->willReturn('gls');

        $this->addProvider($glsProvider);

        $this
            ->shouldThrow(NonUniqueProviderCodeException::create($glsProvider->getWrappedObject()))
            ->during('addProvider', [$glsProvider->getWrappedObject()])
        ;
    }

    function it_shows_all_providers(ProviderInterface $glsProvider): void
    {
        $this->addProvider($glsProvider);

        $this->all()->shouldReturn([$glsProvider]);
    }

    function it_finds_provider_by_class_name(ProviderInterface $glsProvider): void
    {
        $this->addProvider($glsProvider->getWrappedObject());

        $this->findByClassName(get_class($glsProvider->getWrappedObject()))->shouldReturn($glsProvider->getWrappedObject());
    }

    function it_finds_provider_by_code(ProviderInterface $glsProvider): void
    {
        $glsProvider->getCode()->willReturn('gls');

        $this->addProvider($glsProvider);

        $this->findByCode('gls')->shouldReturn($glsProvider);
    }
}
