<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin;

use Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler\RegisterProvidersPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Sylius\Bundle\ResourceBundle\AbstractResourceBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SetonoSyliusPickupPointPlugin extends AbstractResourceBundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterProvidersPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getSupportedDrivers(): array
    {
        return [
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }

    protected function getModelNamespace(): string
    {
        return 'Setono\SyliusPickupPointPlugin\Model';
    }

    protected function getConfigFilesPath(): string
    {
        return sprintf(
            '%s/config/doctrine/%s',
            $this->getPath(),
            strtolower($this->getDoctrineMappingDirectory()),
        );
    }
}
