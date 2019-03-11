<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection;

use Sylius\Bundle\UiBundle\Block\BlockEventListener;
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
        $config = $this->processConfiguration($this->getConfiguration([], $container), $config);

        if (isset($config['post_nord']['api_key'])) {
            $container->setParameter('setono_sylius_pickup_point_post_nord_api_key', $config['post_nord']['api_key']);
            $container->setParameter('setono_sylius_pickup_point_post_nord_mode', $config['post_nord']['mode']);
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');

        if ($config['autoload_javascript']) {
            $container->register(BlockEventListener::class)
                ->addArgument('SetonoSyliusPickupPointPlugin::_javascripts.html.twig')
                ->addTag('kernel.event_listener', [
                    'event' => 'sonata.block.event.sylius.shop.layout.javascripts',
                    'method' => 'onBlockEvent',
                ]);
        }
    }
}
