<?php

declare(strict_types=1);

namespace AppBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

// todo remove this app bundle and include files in tests/Application instead
final class AppBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver(
            [
                realpath(__DIR__ . '/Resources/config/doctrine/model') => 'AppBundle\Model',
            ],
            ['doctrine.orm.entity_manager']
        ));
    }
}
