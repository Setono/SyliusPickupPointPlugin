<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Model;

use PhpSpec\ObjectBehavior;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;

final class PickupPointSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith('gls', '1', 'Post office 14', 'Address', '12345', 'London', 'England', '23N', '180E');
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(PickupPoint::class);
    }

    function it_has_provider_code(): void
    {
        $this->getProviderCode()->shouldReturn('gls');
    }

    function it_has_id(): void
    {
        $this->getId()->shouldReturn('1');
    }

    function it_has_full_id(): void
    {
        $this->getFullId()->shouldReturn('gls-1');
    }

    function it_has_name(): void
    {
        $this->getName()->shouldReturn('Post office 14');
    }

    function it_has_full_name(): void
    {
        $this->getFullName()->shouldReturn('Post office 14, Address, 12345, London');
    }

    function it_has_address(): void
    {
        $this->getAddress()->shouldReturn('Address');
    }

    function it_has_zipcode(): void
    {
        $this->getZipCode()->shouldReturn('12345');
    }

    function it_has_city(): void
    {
        $this->getCity()->shouldReturn('London');
    }

    function it_has_country(): void
    {
        $this->getCountry()->shouldReturn('England');
    }

    function it_has_latitude(): void
    {
        $this->getLatitude()->shouldReturn('23N');
    }

    function it_has_longitude(): void
    {
        $this->getLongitude()->shouldReturn('180E');
    }
}
