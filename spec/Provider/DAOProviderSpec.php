<?php

declare(strict_types=1);

namespace spec\Setono\SyliusPickupPointPlugin\Provider;

use PhpSpec\ObjectBehavior;
use Setono\DAO\Client\ClientInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointCode;
use Setono\SyliusPickupPointPlugin\Provider\DAOProvider;
use Setono\SyliusPickupPointPlugin\Provider\ProviderInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;

class DAOProviderSpec extends ObjectBehavior
{
    public function let(ClientInterface $client): void
    {
        $this->beConstructedWith($client);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(DAOProvider::class);
    }

    public function it_implements_provider_interface(): void
    {
        $this->shouldImplement(ProviderInterface::class);
    }

    public function it_finds_one_pickup_point(ClientInterface $client): void
    {
        $client->get('/DAOPakkeshop/FindPakkeshop.php', [
            'shopid' => '1234',
        ])->willReturn([
            'resultat' => [
                'pakkeshops' => [$this->pickupPointArray('1234')],
            ],
        ]);

        $pickupPoint = $this->findPickupPoint(new PickupPointCode('1234', 'dao', 'DK'));
        $pickupPoint->shouldBeAnInstanceOf(PickupPoint::class);

        $this->testPickupPoint($pickupPoint, '1234');
    }

    public function it_finds_multiple_pickup_points(
        ClientInterface $client,
        OrderInterface $order,
        AddressInterface $address
    ): void {
        $client->get('/DAOPakkeshop/FindPakkeshop.php', [
            'postnr' => 'AE 8000',
            'adresse' => 'Street 10',
            'antal' => 10,
        ])->willReturn([
            'resultat' => [
                'pakkeshops' => [$this->pickupPointArray('0'), $this->pickupPointArray('1')],
            ],
        ]);

        $address->getPostcode()->willReturn('AE 8000');
        $address->getStreet()->willReturn('Street 10');

        $order->getShippingAddress()->willReturn($address);

        $pickupPoints = $this->findPickupPoints($order);
        $pickupPoints->shouldBeArrayOfPickupPoints('0', '1'); // these are the ids to match
    }

    public function getMatchers(): array
    {
        return [
            'beArrayOfPickupPoints' => static function ($pickupPoints, ...$ids) {
                foreach ($pickupPoints as $idx => $element) {
                    if (!$element instanceof PickupPoint) {
                        return false;
                    }

                    if ($element->getCode()->getIdPart() !== $ids[$idx]) {
                        return false;
                    }
                }

                return true;
            },
        ];
    }

    /**
     * @param PickupPoint $pickupPoint
     */
    private function testPickupPoint($pickupPoint, string $id): void
    {
        $code = $pickupPoint->getCode();
        $code->shouldBeAnInstanceOf(PickupPointCode::class);
        $code->getIdPart()->shouldReturn($id);

        $pickupPoint->getName()->shouldReturn('Mediabox');
        $pickupPoint->getAddress()->shouldReturn('Bilka Vejle 20');
        $pickupPoint->getZipCode()->shouldReturn('7100');
        $pickupPoint->getCity()->shouldReturn('Vejle');
        $pickupPoint->getCountry()->shouldReturn('DK');
        $pickupPoint->getLatitude()->shouldReturn('55.7119');
        $pickupPoint->getLongitude()->shouldReturn('9.539939');
    }

    private function pickupPointArray(string $id): array
    {
        return [
            'shopId' => $id,
            'navn' => 'Mediabox',
            'adresse' => 'Bilka Vejle 20',
            'postnr' => '7100',
            'bynavn' => 'Vejle',
            'udsortering' => 'E',
            'latitude' => '55.7119',
            'longitude' => '9.539939',
            'afstand' => 2.652,
            'aabningstider' => [
                'man' => '08:00 - 22:00',
                'tir' => '08:00 - 22:00',
                'ons' => '08:00 - 22:00',
                'tor' => '08:00 - 22:00',
                'fre' => '08:00 - 24:00',
                'lor' => '10:00 - 24:00',
                'son' => '10:00 - 22:00',
            ],
        ];
    }
}
