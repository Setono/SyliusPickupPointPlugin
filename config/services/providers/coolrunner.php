<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Setono\SyliusPickupPointPlugin\Provider\CoolRunnerProvider;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $carriers = [
        'bpost',
        'bring',
        'bringse',
        'colisprive',
        'dhl',
        'dhlpaket',
        'dhlconnect',
        'helthjem',
        'hermes',
        'gls',
        'instabox',
        'instahome',
        'mondialrelay',
        'mtd',
        'postnl',
        'postnord',
        'royalmail',
    ];

    foreach ($carriers as $carrier) {
        $code = 'coolrunner_' . $carrier;

        $services->set('setono_sylius_pickup_point.provider.' . $code, CoolRunnerProvider::class)
            ->args([
                service('setono_coolrunner.client.default'),
                $carrier,
            ])
            ->tag('setono_sylius_pickup_point.provider', [
                'code' => $code,
                'label' => 'setono_sylius_pickup_point.provider.' . $code,
            ])
        ;
    }
};
