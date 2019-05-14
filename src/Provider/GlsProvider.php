<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\Provider;

use Setono\SyliusPickupPointPlugin\Client\GlsSoapClientInterface;
use Setono\SyliusPickupPointPlugin\Model\PickupPoint;
use Setono\SyliusPickupPointPlugin\Model\PickupPointInterface;
use SoapClient;
use SoapFault;
use stdClass;
use Sylius\Component\Core\Model\OrderInterface;

final class GlsProvider implements ProviderInterface
{
    public function findPickupPoints(OrderInterface $order): array
    {
        if (null === $order->getShippingAddress()) {
            return [];
        }

        /** @var GlsSoapClientInterface $client */
        $client = $this->getClient();

        try {
            $result = $client->SearchNearestParcelShops([
                'street' => $order->getShippingAddress()->getStreet(),
                'zipcode' => $order->getShippingAddress()->getPostcode(),
                'countryIso3166A2' => $order->getShippingAddress()->getCountryCode(),
                'Amount' => 10,
            ]);
        } catch (SoapFault $e) {
            return [];
        }

        if (!$result instanceof stdClass || !isset($result->SearchNearestParcelShopsResult->parcelshops) || empty($result->SearchNearestParcelShopsResult->parcelshops->PakkeshopData)) {
            return [];
        }

        $pickupPoints = [];
        foreach ($result->SearchNearestParcelShopsResult->parcelshops->PakkeshopData as $item) {
            $pickupPoints[] = new PickupPoint($item->Number, $item->CompanyName, $item->Streetname, $item->ZipCode, $item->CityName, $item->CountryCodeISO3166A2, $item->Latitude, $item->Longitude);
        }

        return $pickupPoints;
    }

    public function getPickupPointById(string $id): ?PickupPointInterface
    {
        /** @var GlsSoapClientInterface $client */
        $client = $this->getClient();

        try {
            $parcelShop = $client->GetOneParcelShop([
                'ParcelShopNumber' => $id,
            ]);
        } catch (SoapFault $e) {
            return null;
        }
        $data = $parcelShop->GetOneParcelShopResult;
        $pickupPoint = new PickupPoint($data->Number, $data->CompanyName, $data->Streetname, $data->ZipCode, $data->CityName, $data->CountryCodeISO3166A2, $data->Latitude, $data->Longitude);

        return $pickupPoint;
    }

    public function getClient(): SoapClient
    {
        return new SoapClient('http://www.gls.dk/webservices_v4/wsShopFinder.asmx?WSDL');
    }

    public function getCode(): string
    {
        return 'gls';
    }

    public function getName(): string
    {
        return 'GLS';
    }

    public function isEnabled(): bool
    {
        return true;
    }
}
