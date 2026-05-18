<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection;

use LogicException;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Webmozart\Assert\Assert;

final class SetonoSyliusPickupPointExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var array{
         *     driver: string,
         *     resources: array<string, mixed>,
         *     cache: array{enabled: bool, pool: ?string},
         *     local: bool,
         *     providers: array{faker: bool, budbee: bool, coolrunner: bool, dao: bool, gls: bool, post_nord: bool}
         * } $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $container->setParameter('setono_sylius_pickup_point.local', $config['local']);

        $this->registerResources('setono_sylius_pickup_point', $config['driver'], $config['resources'], $container);

        $loader->load('services.php');

        $bundles = $container->hasParameter('kernel.bundles') ? $container->getParameter('kernel.bundles') : [];
        Assert::isArray($bundles);

        $cacheEnabled = $config['cache']['enabled'];
        if ($cacheEnabled) {
            if (!interface_exists(AdapterInterface::class)) {
                throw new LogicException('Using cache is only supported when symfony/cache is installed.');
            }

            if (null === $config['cache']['pool']) {
                throw new LogicException('You should specify pool in order to use cache for pickup point providers.');
            }

            $container->setAlias('setono_sylius_pickup_point.cache', $config['cache']['pool']);
        }

        $container->setParameter('setono_sylius_pickup_point.cache.enabled', $cacheEnabled);

        if ($config['providers']['faker']) {
            if ('prod' === $container->getParameter('kernel.environment')) {
                throw new LogicException("You can't use faker provider in production environment.");
            }

            $loader->load('services/providers/faker.php');
        }

        if ($config['providers']['budbee']) {
            if (!isset($bundles['SetonoBudbeeBundle'])) {
                throw new LogicException('You should use SetonoBudbeeBundle or disable budbee provider.');
            }

            $loader->load('services/providers/budbee.php');
        }

        if ($config['providers']['coolrunner']) {
            if (!isset($bundles['SetonoCoolRunnerBundle'])) {
                throw new LogicException('You should use SetonoCoolRunnerBundle or disable coolrunner provider.');
            }

            $loader->load('services/providers/coolrunner.php');
        }

        if ($config['providers']['dao']) {
            if (!isset($bundles['SetonoDAOBundle'])) {
                throw new LogicException('You should use SetonoDAOBundle or disable dao provider.');
            }

            $loader->load('services/providers/dao.php');
        }

        if ($config['providers']['gls']) {
            if (!isset($bundles['SetonoGlsWebserviceBundle'])) {
                throw new LogicException('You should use SetonoGlsWebserviceBundle or disable gls provider.');
            }

            $loader->load('services/providers/gls.php');
        }

        if ($config['providers']['post_nord']) {
            if (!isset($bundles['SetonoPostNordBundle'])) {
                throw new LogicException('You should use SetonoPostNordBundle or disable post_nord provider.');
            }

            $loader->load('services/providers/post_nord.php');
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'buses' => [
                    'setono_sylius_pickup_point.command_bus' => null,
                ],
            ],
        ]);

        $container->prependExtensionConfig('sylius_twig_hooks', [
            'hooks' => [
                'sylius_admin.base#javascripts' => [
                    'setono_sylius_pickup_point.javascripts' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/_javascripts.html.twig',
                        'priority' => -100,
                    ],
                ],
                'sylius_shop.base#javascripts' => [
                    'setono_sylius_pickup_point.javascripts' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/_javascripts.html.twig',
                        'priority' => -100,
                    ],
                ],
                'sylius_admin.order.show.content.sections.shipments.item' => [
                    'pickup_point' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/Shop/Label/Shipment/pickupPoint.html.twig',
                        'priority' => 150,
                    ],
                ],
            ],
        ]);
    }
}
