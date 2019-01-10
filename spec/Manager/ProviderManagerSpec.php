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
    public function let(ProviderInterface $provider): void
    {
        $provider->getCode()->willReturn('gls');

        $this->beConstructedWith($provider);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProviderManager::class);
    }

    public function it_implements_an_provider_manager_interface(): void
    {
        $this->shouldImplement(ProviderManagerInterface::class);
    }

    public function it_throws_an_exception_if_provider_code_is_not_unique(ProviderInterface $provider1, ProviderInterface $provider2): void
    {

        $provider1->getCode()->willReturn('gls');
        $provider2->getCode()->willReturn('gls');

        $this->beConstructedWith($provider1, $provider2);

        $this->shouldThrow(NonUniqueProviderCodeException::class)->duringInstantiation();
    }

    public function it_shows_all_providers(ProviderInterface $provider): void
    {
        $this->all()->shouldReturn(['gls' => $provider]);
    }

    public function it_finds_provider_by_class_name(ProviderInterface $provider): void
    {
        $this->findByClassName(get_class($provider->getWrappedObject()))->shouldReturn($provider->getWrappedObject());
    }

    public function it_finds_provider_by_code(ProviderInterface $provider): void
    {
        $this->findByCode('gls')->shouldReturn($provider);
    }
}
