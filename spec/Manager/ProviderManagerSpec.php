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
    public function let(ProviderInterface $provider1, ProviderInterface $provider2): void
    {
        $provider1->isEnabled()->willReturn(true);
        $provider1->getCode()->willReturn('p1');
        $provider2->isEnabled()->willReturn(true);
        $provider2->getCode()->willReturn('p2');

        $this->beConstructedWith($provider1, $provider2);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProviderManager::class);
    }

    public function it_implements_an_provider_manager_interface(): void
    {
        $this->shouldImplement(ProviderManagerInterface::class);
    }

    public function it_throws_an_exception_if_provider_code_is_not_unique(ProviderInterface $provider1, ProviderInterface $provider12): void
    {
        $provider1->isEnabled()->willReturn(true);
        $provider1->getCode()->willReturn('p1');
        $provider12->isEnabled()->willReturn(true);
        $provider12->getCode()->willReturn('p1');

        $this->beConstructedWith($provider1, $provider12);

        $this->shouldThrow(NonUniqueProviderCodeException::class)->duringInstantiation();
    }

    public function it_shows_all_providers(ProviderInterface $provider1, ProviderInterface $provider2): void
    {
        $this->beConstructedWith($provider1, $provider2);

        $this->all()->shouldReturn(['p1' => $provider1, 'p2' => $provider2]);
    }

    public function it_finds_provider_by_class_name(ProviderInterface $provider1): void
    {
        $this->findByClassName(get_class($provider1->getWrappedObject()))->shouldReturn($provider1->getWrappedObject());
    }

    public function it_finds_provider_by_code(ProviderInterface $provider1): void
    {
        $this->findByCode('p1')->shouldReturn($provider1);
    }
}
