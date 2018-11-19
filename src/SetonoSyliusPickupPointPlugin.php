<?php

declare(strict_types=1);

namespace Setono\SyliusPickupPointPlugin;

use Setono\SyliusPickupPointPlugin\DependencyInjection\Compiler\ProviderPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SetonoSyliusPickupPointPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ProviderPass());
    }
}
