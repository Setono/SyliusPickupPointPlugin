<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->addPathToExclude(__DIR__ . '/tests')
    // Carrier providers reference classes from `suggest`-only bundles.
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/DAOProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/GlsProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/Provider/PostNordProvider.php', [ErrorType::UNKNOWN_CLASS])
    ->ignoreErrorsOnPath(__DIR__ . '/src/DependencyInjection/Configuration.php', [ErrorType::UNKNOWN_CLASS])
;
