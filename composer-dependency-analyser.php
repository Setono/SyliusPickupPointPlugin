<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->addPathToExclude(__DIR__ . '/tests')
    // Carrier providers reference classes from `suggest`-only bundles.
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/BudbeeProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/CoolRunnerProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/DAOProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/GlsProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/PostNordProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/DependencyInjection/Configuration.php', [ErrorType::UNKNOWN_CLASS])
    // sylius/* component classes are used at the type-hint level via interfaces re-exposed by
    // sylius/core, sylius/order, sylius/shipping, etc.; the analyser only sees the FQCN in
    // type hints. Same for symfony/routing (RouterInterface available via security-bundle) and
    // twig/twig (provided via templates rather than direct `use`).
    ->ignoreErrorsOnPackages([
        'sylius/core',
        'sylius/core-bundle',
        'sylius/order',
        'sylius/shipping',
        'sylius/shipping-bundle',
        'symfony/routing',
        'symfony/security-bundle',
        'twig/twig',
    ], [ErrorType::UNUSED_DEPENDENCY])
    // sylius/sylius is a dev meta-package; the trait it ships is also exported by sylius/core-bundle.
    ->ignoreErrorsOnPackage('sylius/sylius', [ErrorType::DEV_DEPENDENCY_IN_PROD])
;
