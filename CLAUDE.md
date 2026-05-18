# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Sylius plugin (`setono/sylius-pickup-point-plugin`, type `sylius-plugin`) that adds a pickup-point `<select>` to the shipping checkout step. Supports DAO, GLS, PostNord, Budbee, CoolRunner, and a `faker` provider for dev. Each third-party provider lives behind an optional `setono/*-bundle` dependency and is only enabled when both configured and (by default) when its bundle class is present — see `src/DependencyInjection/Configuration.php`.

The plugin code is in `src/`; `tests/Application/` is a full Sylius Symfony app used by phpunit, behat and the integration suite (its `bin/console` is the way to run Symfony commands against this plugin).

## Commands

All commands run from the repo root unless noted. Composer scripts wrap most things:

- `composer phpunit` — run unit tests (`phpunit.xml.dist`, suite covers `tests/`)
- `composer phpspec` — run phpspec specs in `spec/`
- `composer behat` — run Behat (needs the integration setup below; the script forces `-d memory_limit=-1` and progress format)
- `composer tests` — phpspec + behat
- `composer analyse` — Psalm
- `composer check-style` / `composer fix-style` — ECS (Sylius Labs ruleset, see `ecs.php`)
- `composer checks` — validate, normalize dry-run, check-style, analyse (matches the "Coding Standards" CI job)
- `composer fixtures` — `cd tests/Application && bin/console sylius:fixtures:load` (honors `SYMFONY_ENV`)

Single-test invocations (use the project-local `vendor/bin`):

- `vendor/bin/phpunit --filter SomeTest` or `vendor/bin/phpunit tests/Path/To/SomeTest.php`
- `vendor/bin/phpspec run spec/Path/To/SomeSpec.php`
- `vendor/bin/behat features/shop/some.feature:42`
- `vendor/bin/rector process --dry-run` (CI runs this `continue-on-error`)

Symfony app inside `tests/Application/`:

- `(cd tests/Application && bin/console <cmd>)` — schema diff/migrate, lint:container, lint:yaml, lint:twig, sylius:install:assets, etc.
- Yarn assets: `(cd tests/Application && yarn install && yarn build)`. `node_modules` at the repo root is a symlink to `tests/Application/node_modules`.

Behat integration setup (mirrors CI in `.github/workflows/build.yaml`): start MySQL, create DB + schema in `tests/Application/`, install/build yarn assets, run `symfony server:start --port=8080 --dir=public --daemon`, run headless Chrome on `127.0.0.1:9222`. The Behat base URL is `https://127.0.0.1:8080/` (see `behat.yml.dist`).

The plugin's own console command (run inside `tests/Application/`): `bin/console setono-sylius-pickup-point:load-pickup-points [provider]` — dispatches `LoadPickupPoints` messages to populate the local DB.

## Architecture

**Providers are the core abstraction.** `Setono\SyliusPickupPointPlugin\Provider\ProviderInterface` is implemented by one concrete class per carrier (`DAOProvider`, `GlsProvider`, `PostNordProvider`, `BudbeeProvider`, `CoolRunnerProvider`, `FakerProvider`). Each is registered as a service tagged `setono_sylius_pickup_point.provider` with `code` and `label` attributes.

**Provider decoration happens in a compiler pass.** `DependencyInjection/Compiler/RegisterProvidersPass` reads the tagged services and wraps each one in two optional decorators before registering with the `setono_sylius_pickup_point.registry.provider` (a Sylius `ServiceRegistry`):

1. `CachedProvider` (priority 256) — wraps when `cache.enabled: true`; keys per-order on country+postcode+street.
2. `LocalProvider` (priority 512, outermost) — wraps when `local: true` (default); on `TimeoutException` from the underlying provider, falls back to `PickupPointRepository` (the rows populated by `LoadPickupPointsHandler`).

When adding/changing a provider, the work happens in three places: the `Provider/` class, a service definition in `src/Resources/config/services/provider/`, and (usually) a corresponding `setono/*-bundle` `class_exists` check in `Configuration.php`. Do not register decorators yourself — the compiler pass does it based on plugin config.

**Identity uses `PickupPointCode`.** `Model/PickupPointCode` is a value object serialized as `provider---id---country` (the country part is necessary because some carriers only guarantee id uniqueness per country). `createFromString()` parses that format; this is the on-the-wire form between the form/JS layer and PHP.

**Async loading.** `Command/LoadPickupPointsCommand` dispatches one `Message/Command/LoadPickupPoints` per provider over `symfony/messenger`; `Message/Handler/LoadPickupPointsHandler` calls `findAllPickupPoints()` on the provider and upserts into the `PickupPointRepository`, flushing+clearing every 50 entities. This is the data source for `LocalProvider`'s fallback.

**Resource extension model (Sylius pattern).** Consumers extend `Sylius\Component\Core\Model\Shipment` with `PickupPointAwareTrait` (and implement `ShipmentInterface`) and similarly `ShippingMethod` with `PickupPointProviderAwareTrait`. The plugin's own `PickupPoint` resource is defined via `SyliusResourceBundle` (only ORM driver supported — see `SetonoSyliusPickupPointPlugin::getSupportedDrivers()`).

## Constraints worth knowing

- PHP `>=8.1`; CI matrix is PHP 8.1/8.2 × Symfony 5.4/6.4 × lowest/highest deps. Don't use 8.3+ syntax. (Coding-standards job pins 8.1 specifically to catch syntax that wouldn't parse on the lower bound.)
- The `composer checks` job validates `composer.json` *and* runs `composer normalize --dry-run` — after editing `composer.json`, run `composer normalize` locally.
- Yaml/Twig linting in CI runs through `tests/Application/bin/console lint:yaml ../../src/Resources` and the equivalent `lint:twig` — keep `src/Resources/**` valid against the test kernel.
- `dependency-analysis` job runs `composer-require-checker` and `composer-unused` on a `require-dev`-stripped composer.json — only declare runtime deps that are actually used in `src/`, and don't leave unused ones.
