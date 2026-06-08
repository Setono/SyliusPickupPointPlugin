# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Sylius plugin (`setono/sylius-pickup-point-plugin`, type `sylius-plugin`) that adds a pickup-point chooser to the shipping checkout step: once the page has rendered, a small framework-free script loads the points asynchronously and renders them as radio buttons (no `<select>`, no Stimulus). Supports DAO, GLS, PostNord, and a `faker` provider for dev. Each third-party provider lives behind an optional `setono/*-bundle` dependency and is only enabled when both configured and (by default) when its bundle class is present — see `src/DependencyInjection/Configuration.php`.

The plugin code is in `src/`; `tests/Application/` is a full Sylius Symfony app used by phpunit, behat and the integration suite (its `bin/console` is the way to run Symfony commands against this plugin).

## Commands

> **PHP version — use the versioned binary directly, never switch the global default.**
> This repo's `vendor/` is locked to PHP `>= 8.4` (composer.lock was resolved on 8.4),
> so the shell default — which is often an older version like 8.1 — fails Composer's
> platform check. Do **not** run the `8.1`/`8.2`/`8.3`/`8.4` switcher aliases from
> `~/.zshrc`: they `brew unlink`/`brew link --force` and change the *system-wide*
> default PHP, which can break other work in progress on this machine. Instead invoke
> the matching binary directly, e.g.
> `"$(brew --prefix php@8.4)/bin/php" vendor/bin/rector process --dry-run`
> (or `composer`, `phpunit`, `phpstan`, …). Prefix any tooling that hits the platform
> check the same way.

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
- JS unit tests (the checkout chooser): `yarn --cwd tests/Application test` (Vitest + jsdom; specs in `tests/Application/tests/js/`). Config is `vitest.config.mjs` — NOT `type: module`, because Encore's `webpack.config.js` is CommonJS. The CI `javascript-tests` job runs a composer install first since `tests/Application/package.json` has `file:` deps on `vendor/`.

Behat integration setup (mirrors CI in `.github/workflows/build.yaml`): start MySQL, create DB + schema in `tests/Application/`, install/build yarn assets, run `symfony server:start --port=8080 --dir=public --daemon`, run headless Chrome on `127.0.0.1:9222`. The Behat base URL is `https://127.0.0.1:8080/` (see `behat.yml.dist`).

## Architecture

**Providers are the core abstraction.** `Setono\SyliusPickupPointPlugin\Provider\ProviderInterface` is implemented by one concrete class per carrier (`DAOProvider`, `GlsProvider`, `PostNordProvider`, `FakerProvider`). Each is registered as a service tagged `setono_sylius_pickup_point.provider` — usually via the `#[AsProvider(code, name)]` attribute — and exposes `findPickupPoints(Address)` (the list for an order address) and `findPickupPoint(id, metadata)`.

**Provider registration happens in a compiler pass.** `DependencyInjection/Compiler/RegisterProvidersPass` reads the tagged services, stamps each provider's `code` onto it (`setCode()`), registers it into the `setono_sylius_pickup_point.registry.provider` (`ProviderRegistry`, a Sylius `ServiceRegistry`), exposes the code→name map as the `setono_sylius_pickup_point.providers` parameter, and rejects duplicate codes (`NonUniqueProviderCodeException`). Each provider is also made **lazy via interface proxifying** (`setLazy(true)` + a `proxy` tag for `ProviderInterface`) so a provider whose constructor reaches an external service (e.g. GLS opening a SOAP client) is built only when first called; because the providers are `final`, the proxy *implements the interface* instead of subclassing the class — required for lazy services on PHP < 8.4. There are **no provider decorators**: the 1.x `CachedProvider`/`LocalProvider` and the messenger-driven local DB snapshot (`LoadPickupPoints*`, `PickupPointRepository`) were removed in 2.0 (see `UPGRADE.md`) — providers are now called live.

When adding/changing a provider, the work happens in three places: the `Provider/` class, a service definition in `config/services/providers/`, and (usually) a corresponding `setono/*-bundle` `class_exists` check in `Configuration.php`.

**Checkout pickup-point selection (async, no framework).** This is the heart of the shop UX and it deliberately makes **zero provider calls while the shipping page renders**, so a slow or down carrier can never stall checkout:

- `Form/Extension/ShipmentTypeExtension` adds a hidden `pickupPoint` field (`Form/Type/PickupPointType`, a `HiddenType` + `Form/DataTransformer/PickupPointTransformer`). `Form/Extension/ShippingMethodChoiceTypeExtension` stamps `data-pickup-point-provider` onto each pickup-capable shipping-method radio.
- After the page is on screen, `public/js/setono-pickup-point.js` — a framework-free native **ES module** (`<script type="module">`, no bundler) wired via the `_javascripts` twig hook on `sylius_shop.checkout#javascripts` — fetches `GET /pickup-points` (`Controller/Action/PickupPointsAction`) **once**. That endpoint returns each pickup-capable method's points for the current cart, keyed by method code, with a per-provider `try/catch` so one failing carrier doesn't take the others down. The module's `PickupPointChooser` class builds the chooser by **cloning the `<template>`s** rendered by `templates/shop/checkout/_pickup_point_templates.html.twig` (all markup, translations and styling live there — there is no JS-built markup; a missing template just renders nothing), toggles the visible group as the shipping method changes, and shows loading/empty/error states. It (re-)initialises on both `DOMContentLoaded` and `turbo:load` because Sylius' shop navigates with Turbo (checkout steps are AJAX body swaps). It is extensible without forking — subclass `PickupPointChooser` (register it on `window.SetonoSyliusPickupPointChooser`), listen for the bubbling `setono:pickup-point(s):*` CustomEvents, or set `window.setonoSyliusPickupPointConfig`; see `docs/customizing-the-chooser.md`. JS unit tests run under Vitest/jsdom: `yarn --cwd tests/Application test`.
- Each radio's value is a `value` token — the whole `DTO/PickupPoint` base64url-encoded by `Encoder/PickupPointEncoder`. Selecting a radio writes the token into the hidden field; on submit `PickupPointTransformer` decodes it straight back into the `PickupPoint` (no provider call, no re-resolve, no faker drift) and `Model/PickupPointAwareTrait::setPickupPoint()` persists it to the `pickup_point` JSON column. `Validator/Constraints/HasPickupPointSelected` enforces that a pickup-capable method actually got a point.

**Identity lives on the DTO.** `DTO/PickupPoint` is a plain final class with public properties (`provider`, `id`, `country`, `name`, …, plus an open `metadata` map) and `fromArray()`/`jsonSerialize()` — not a Doctrine resource. The 1.x `Model/PickupPoint*` resources and the `PickupPointCode` (`provider---id---country`) value object were removed in 2.0.

**Resource extension model (Sylius pattern).** Consumers extend `Sylius\Component\Core\Model\Shipment` with `PickupPointAwareTrait` (and implement `ShipmentInterface`) and `ShippingMethod` with `PickupPointProviderAwareTrait` (both `#[ORM\Column]` attribute mappings). The plugin owns no Doctrine resource of its own anymore.

## Working agreements

- **Don't commit unless I say so.** Stage and run local checks freely, but
  wait for an explicit instruction before running `git commit` (and before
  `git push`).
- **Verify UI changes with Playwright.** After any change that affects the
  rendered admin/shop UI — twig hooks, form rendering, the shop JS, the
  AJAX endpoints, checkout flow — drive the test app in a browser via the
  `mcp__playwright__browser_*` tools (`browser_navigate`, `browser_snapshot`,
  `browser_console_messages`, `browser_network_requests`, etc.) before
  reporting the task done. Type checking and unit tests verify code
  correctness, not feature correctness; the UI flow is the only check
  that catches twig-hook misconfiguration, broken JSON shape, or missing
  asset wiring.
- **Always translate new message keys into every supported locale.** When you
  add a key under `translations/` (`messages.*.yml`, `validators.*.yml`),
  provide it in all of these locales, not just `en`/`da`:
  - Nordic: Danish (`da`), Swedish (`sv`), Norwegian (`no`), Finnish (`fi`)
  - Large EU: German (`de`), French (`fr`), Spanish (`es`), Italian (`it`), Dutch (`nl`), Polish (`pl`)
  - Other common Sylius locales: Portuguese (`pt`), Czech (`cs`), Hungarian (`hu`), Romanian (`ro`), Ukrainian (`uk`)

## Constraints worth knowing

- PHP `>=8.2`; CI matrix is PHP 8.2/8.3/8.4 × Symfony 6.4/7.4 × lowest/highest deps. Keep to 8.2-compatible syntax — 8.2 is the floor.
- The `composer checks` job validates `composer.json` *and* runs `composer normalize --dry-run` — after editing `composer.json`, run `composer normalize` locally.
- The plugin's templates and config live at repo-root `templates/` and `config/` (moved out of `src/Resources/` in the 2.x layout). Lint twig with `(cd tests/Application && bin/console lint:twig ../../templates)`, valid against the test kernel.
- `dependency-analysis` job runs `composer-require-checker` and `composer-unused` on a `require-dev`-stripped composer.json — only declare runtime deps that are actually used in `src/`, and don't leave unused ones.
