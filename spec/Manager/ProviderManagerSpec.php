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
    public function let(ProviderInterface $providerGls, ProviderInterface $providerPostNord): void
    {
        $providerGls->isEnabled()->willReturn(true);
        $providerGls->getCode()->willReturn('gls');
        $providerPostNord->isEnabled()->willReturn(true);
        $providerPostNord->getCode()->willReturn('postnord');

        $this->beConstructedWith($providerGls, $providerPostNord);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProviderManager::class);
    }

    public function it_implements_an_provider_manager_interface(): void
    {
        $this->shouldImplement(ProviderManagerInterface::class);
    }

    public function it_throws_an_exception_if_provider_code_is_not_unique(ProviderInterface $providerGls, ProviderInterface $providerGls2): void
    {
        $providerGls->isEnabled()->willReturn(true);
        $providerGls->getCode()->willReturn('gls');
        $providerGls2->isEnabled()->willReturn(true);
        $providerGls2->getCode()->willReturn('gls');

        $this->beConstructedWith($providerGls, $providerGls2);

        $this->shouldThrow(NonUniqueProviderCodeException::class)->duringInstantiation();
    }

    public function it_shows_all_providers(ProviderInterface $providerGls, ProviderInterface $providerPostNord): void
    {

        $this->beConstructedWith($providerGls, $providerPostNord);

        $this->all()->shouldReturn(['gls' => $providerGls, 'postnord' => $providerPostNord]);
    }

    public function it_finds_provider_by_class_name(ProviderInterface $providerGls): void
    {
        $this->findByClassName(get_class($providerGls->getWrappedObject()))->shouldReturn($providerGls->getWrappedObject());
    }

    public function it_finds_provider_by_code(ProviderInterface $providerGls): void
    {
        $this->findByCode('gls')->shouldReturn($providerGls);
    }
}
