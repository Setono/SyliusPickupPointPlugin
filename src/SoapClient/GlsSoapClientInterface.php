<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\SoapClient;

interface GlsSoapClientInterface
{
    public function GetOneParcelShop(array $params): \stdClass;

    public function SearchNearestParcelShops(array $params): \stdClass;
}
