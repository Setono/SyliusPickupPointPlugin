<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin\DependencyInjection;

use LogicException;
use Setono\SyliusPickupPointPlugin\Attribute\AsProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Webmozart\Assert\Assert;

final class SetonoSyliusPickupPointExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var array{
         *     providers: array{faker: bool, dao: bool, gls: bool, post_nord: bool}
         * } $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));

        $loader->load('services.php');

        $container->registerAttributeForAutoconfiguration(
            AsProvider::class,
            static function (ChildDefinition $definition, AsProvider $attribute): void {
                $definition->addTag('setono_sylius_pickup_point.provider', [
                    'code' => $attribute->code,
                    'name' => $attribute->name,
                ]);
            },
        );

        $bundles = $container->hasParameter('kernel.bundles') ? $container->getParameter('kernel.bundles') : [];
        Assert::isArray($bundles);

        if ($config['providers']['faker']) {
            if ('prod' === $container->getParameter('kernel.environment')) {
                throw new LogicException("You can't use faker provider in production environment.");
            }

            $loader->load('services/providers/faker.php');
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
        $container->prependExtensionConfig('sylius_twig_hooks', [
            'hooks' => [
                'sylius_admin.order.show.content.sections.shipments.item' => [
                    'pickup_point' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/shop/label/shipment/pickupPoint.html.twig',
                        'priority' => 150,
                    ],
                ],
                'sylius_admin.shipping_method.create.content.form.configuration' => [
                    'pickup_point_provider' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/admin/shipping_method/form/configuration/pickupPointProvider.html.twig',
                        'priority' => 50,
                    ],
                ],
                'sylius_admin.shipping_method.update.content.form.configuration' => [
                    'pickup_point_provider' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/admin/shipping_method/form/configuration/pickupPointProvider.html.twig',
                        'priority' => 50,
                    ],
                ],
                'sylius_shop.checkout.select_shipping.content.form.shipments.shipment' => [
                    'pickup_point' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/shop/checkout/select_shipping/shipment/pickupPoint.html.twig',
                        'priority' => -100,
                    ],
                ],
                'sylius_shop.base#javascripts' => [
                    'setono_sylius_pickup_point.javascripts' => [
                        'template' => '@SetonoSyliusPickupPointPlugin/_javascripts.html.twig',
                        'priority' => -100,
                    ],
                ],
            ],
        ]);
    }
}
