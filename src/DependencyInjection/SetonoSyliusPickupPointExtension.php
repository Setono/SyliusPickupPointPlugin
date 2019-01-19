<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusPickupPointExtension extends Extension
{
    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(/** @scrutinizer ignore-type */$this->getConfiguration([], $container), $config);

        if (isset($config['postnord']['api_key'])) {
            $container->setParameter('setono_sylius_pickup_point_postnord_apikey', $config['postnord']['api_key']);
            $container->setParameter('setono_sylius_pickup_point_postnord_mode', $config['postnord']['mode']);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');
    }
}
