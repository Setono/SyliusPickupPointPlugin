# Upgrade from 1.x to 2.0

## Requirements

| Component | 1.x                      | 2.0                  |
|-----------|--------------------------|----------------------|
| PHP       | `>=8.1`                  | `>=8.2`              |
| Symfony   | `^5.4 \|\| ^6.0`         | `^6.4 \|\| ^7.4`     |
| Sylius    | `^1.0`                   | `^2.0`               |
| Doctrine ORM | `^2.7`                | `^3.0`               |

## Removed runtime dependencies

| Package | Replacement |
|---------|-------------|
| `friendsofsymfony/rest-bundle` | `symfony/serializer` (Symfony Serializer + `JsonResponse`) |
| `doctrine/event-manager` | Pulled transitively by Doctrine ORM 3 |
| `behat/transliterator` | Removed along with `CachedProvider` |
| `psr/cache`, `symfony/cache` | Removed along with `CachedProvider` |
| `symfony/messenger` | Removed along with `LoadPickupPointsHandler` |
| `symfony/console` | Removed along with `LoadPickupPointsCommand` |
| `sylius/resource-bundle` | Removed (no plugin-owned resource anymore) |

The plugin no longer depends on FOSRestBundle or JMS Serializer. The AJAX
endpoints now return a `JsonResponse` produced by
`Symfony\Component\Serializer\SerializerInterface`. Each pickup point is
serialized from the public properties of
`Setono\SyliusPickupPointPlugin\DTO\PickupPoint` plus an `identifier` field —
the opaque, URL/form-safe token produced by `PickupPointIdentifierEncoder` and
added by `PickupPointNormalizer`. The shop JS reads `value.identifier` directly
(it no longer composes any identifier format client-side) and still builds
`full_address` from `${address}, ${zipCode} ${city}` in
`public/js/setono-pickup-point.js`.

## Plugin file layout

The plugin moved from `src/Resources/**` to repo-root locations
(matches Sylius 2.x conventions / `setono/sylius-plugin-skeleton`):

| 1.x path                                     | 2.0 path                |
|----------------------------------------------|-------------------------|
| `src/Resources/config/`                      | `config/`               |
| `src/Resources/config/services/*.xml`        | `config/services/*.php` (PHP DSL) |
| `src/Resources/config/services/providers/*.xml` | `config/services/providers/*.php` |
| `src/Resources/config/routing.yaml`          | `config/routes.yaml` (imports `config/routes/shop.yaml`) |
| `src/Resources/config/routing_non_localized.yaml` | `config/routes_no_locale.yaml` |
| `src/Resources/config/doctrine/`             | (removed — no plugin-owned doctrine resource) |
| `src/Resources/config/validation/`           | `config/validation/`    |
| `src/Resources/config/routes/`               | `config/routes/`        |
| `src/Resources/config/app/config.yaml`       | (removed — inlined via `Extension::prepend()`) |
| `src/Resources/config/app/fixtures.yaml`     | (removed — example data, copy into your test app if needed) |
| `src/Resources/config/serializer/PickupPoint.yml` | (removed — the DTO has no serializer metadata, the endpoint emits every public property) |
| `src/Resources/translations/`                | `translations/`         |
| `src/Resources/views/`                       | `templates/`            |
| `src/Resources/public/`                      | `public/`               |

Update any `@SetonoSyliusPickupPointPlugin/Resources/...` references in your
own templates and config to drop the `Resources/` segment.

## Routing import

The routing now follows the `setono/sylius-plugin-skeleton` layout: a top-level
`config/routes.yaml` (localized, prefixed with `/{_locale}`) delegates to the
per-section files under `config/routes/` (`config/routes/shop.yaml`). A
`config/routes_no_locale.yaml` variant is provided for stores with localized
URLs disabled.

```yaml
# config/routes/setono_sylius_pickup_point.yaml

setono_sylius_pickup_point:
    resource: "@SetonoSyliusPickupPointPlugin/config/routes.yaml"
```

Previously: `@SetonoSyliusPickupPointPlugin/Resources/config/routing.yaml`.

The plugin-owned shop routes and their URLs are:

- `setono_sylius_pickup_point_shop_ajax_pickup_points_search_by_cart_address`
  → `/{_locale}/ajax/pickup-points/from-cart`
- `setono_sylius_pickup_point_shop_ajax_pickup_point_by_identifier`
  → `/{_locale}/ajax/pickup-points/from-identifier` (takes an `identifier` query parameter)

## Templates → Twig hooks

The plugin now wires its layout JS snippet and the pickup-point shipment label
automatically via `sylius_twig_hooks`. Consumers no longer need to:

- include `@SetonoSyliusPickupPointPlugin/_javascripts.html.twig` manually in
  `layout.html.twig`; the plugin attaches it to `sylius_admin.base#javascripts`
  and `sylius_shop.base#javascripts`.
- include `@SetonoSyliusPickupPointPlugin/shop/label/shipment/pickupPoint.html.twig`
  in admin order-show templates; the plugin attaches it to
  `sylius_admin.order.show.content.sections.shipments.item`.

Drop the matching `{% include … pickupPoint.html.twig %}` blocks from your
custom admin templates if you copied them from the 1.x README.

## Service IDs

Services owned by the plugin now use FQCN service IDs (Sylius 2.x convention).
Examples:

| 1.x ID                                                              | 2.0 ID                                                                       |
|---------------------------------------------------------------------|------------------------------------------------------------------------------|
| `setono_sylius_pickup_point.command.load_pickup_points`             | (removed — see "Removed: local snapshot and message bus")                    |
| `setono_sylius_pickup_point.controller.action.pickup_point_by_id`   | `Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointByIdentifierAction` |
| `setono_sylius_pickup_point.controller.action.pickup_points_search_by_cart_address` | `Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointsSearchByCartAddressAction` |
| `setono_sylius_pickup_point.message.handler.load_pickup_points`     | (removed — see "Removed: local snapshot and message bus")                    |
| `setono_sylius_pickup_point.validator.has_pickup_point_selected`    | `Setono\SyliusPickupPointPlugin\Validator\Constraints\HasPickupPointSelectedValidator` |
| `setono_sylius_pickup_point.fixture.shipping_method`                | `Setono\SyliusPickupPointPlugin\Fixture\ShippingMethodFixture`               |
| `setono_sylius_pickup_point.fixture.example_factory.shipping_method`| `Setono\SyliusPickupPointPlugin\Fixture\Factory\ShippingMethodExampleFactory`|
| `setono_sylius_pickup_point.shipping.order_shipping_method_selection_requirement_checker` | `Setono\SyliusPickupPointPlugin\Shipping\OrderShippingMethodSelectionRequirementChecker` |
| `setono_sylius_pickup_point.block_event_listener.javascript`        | (removed — JS layout snippet now wired through `sylius_twig_hooks`)         |
| `setono_sylius_pickup_point.repository.pickup_point`                | (removed — see "Removed: local snapshot and message bus")                    |

These IDs are still kept (compiler pass and bundle config reference them):

- `setono_sylius_pickup_point.registry.provider`
- `setono_sylius_pickup_point.provider.*` (per-provider services tagged `setono_sylius_pickup_point.provider`)

## Removed: provider cache

The opt-in PSR-cache decorator (`Setono\SyliusPickupPointPlugin\Provider\CachedProvider`)
has been removed in 2.0 along with the `setono_sylius_pickup_point.cache` configuration
key and the `psr/cache` / `symfony/cache` runtime dependencies. Pickup-point lookups
are now served directly by each provider. Drop the following from your application
configuration:

```yaml
# Remove this — no longer supported
setono_sylius_pickup_point:
    cache:
        enabled: true
        pool: setono_sylius_pickup_point.provider_cache_pool

framework:
    cache:
        pools:
            setono_sylius_pickup_point.provider_cache_pool: ~
```

## Removed: local snapshot and message bus

The `LocalProvider` decorator (which fell back to a local DB snapshot of pickup
points when a third-party API timed out) has been removed in 2.0 along with the
infrastructure that populated that snapshot. Specifically the following have all
been removed:

- `Setono\SyliusPickupPointPlugin\Provider\LocalProvider`
- `Setono\SyliusPickupPointPlugin\Command\LoadPickupPointsCommand`
  and the `setono-sylius-pickup-point:load-pickup-points` console command
- `Setono\SyliusPickupPointPlugin\Message\Command\LoadPickupPoints` /
  `Setono\SyliusPickupPointPlugin\Message\Handler\LoadPickupPointsHandler`
  and the `setono_sylius_pickup_point.command_bus` messenger bus
- `Setono\SyliusPickupPointPlugin\Doctrine\ORM\PickupPointRepository` and
  `Setono\SyliusPickupPointPlugin\Repository\PickupPointRepositoryInterface`
- `Setono\SyliusPickupPointPlugin\EventListener\AddIndicesSubscriber`
- `Setono\SyliusPickupPointPlugin\Exception\TimeoutException`
- The plugin-owned `PickupPoint` Doctrine resource and its tables
  (`setono_sylius_pickup_point__pickup_point` / `..._pickup_point_code`)
- The `setono_sylius_pickup_point.local` config key

Drop the following from your application configuration:

```yaml
# Remove this — no longer supported
setono_sylius_pickup_point:
    local: true
```

Generate a migration with `bin/console doctrine:migrations:diff` to drop the
two plugin-owned tables. Each provider still implements `findPickupPoints()`
and `findPickupPoint()` directly against the carrier API.

`Setono\SyliusPickupPointPlugin\Provider\ProviderInterface::findAllPickupPoints()`
was the entry point used by the now-removed `LoadPickupPointsHandler`. Since
nothing in the plugin calls it anymore, it has been dropped from the interface
and from every shipped provider implementation. Custom providers that still
declare it should remove the method to match the interface.

`Setono\SyliusPickupPointPlugin\Model\PickupPoint`,
`Setono\SyliusPickupPointPlugin\Model\PickupPointInterface` and
`Setono\SyliusPickupPointPlugin\Model\PickupPointCode` are removed. The
replacement is `Setono\SyliusPickupPointPlugin\DTO\PickupPoint` — a final
class with public properties (scalars plus an open `metadata` map) and
`fromArray()` / `jsonSerialize()` helpers, but no `#[Groups]` /
`#[SerializedName]` attributes and no Sylius resource behaviour. It's populated
from the carrier API response by each provider, and emitted (with its
`identifier` token) by the AJAX endpoints.

The `PickupPointCode` value object has been inlined as plain `provider`,
`id` and `country` properties on the DTO. The old `provider---id---country`
wire-format string is gone: the identifier is now an opaque, URL/form-safe
token produced by
`Setono\SyliusPickupPointPlugin\Encoder\PickupPointIdentifierEncoder`
(base64url-encoded JSON of `provider`, `id` and an open `metadata` map). The
server emits it as the `identifier` field of each AJAX result and the shop JS
writes it verbatim into the hidden input — it no longer composes any format
client-side.

Provider implementations now resolve a point from its id plus an open
metadata map (into which the well-known country is folded):

```php
public function findPickupPoint(string $id, array $metadata = []): ?PickupPoint;
```

Consumers that hand-built or type-hinted `PickupPointInterface` should
switch to the new DTO and replace setter calls with direct property
assignment:

```php
// 1.x / early 2.x
$pickupPoint = new \Setono\SyliusPickupPointPlugin\Model\PickupPoint();
$pickupPoint->setCode(new \Setono\SyliusPickupPointPlugin\Model\PickupPointCode('abc', 'gls', 'DK'));
$pickupPoint->setName('Aalborg Centrum');

// 2.x
$pickupPoint = new \Setono\SyliusPickupPointPlugin\DTO\PickupPoint();
$pickupPoint->provider = 'gls';
$pickupPoint->id = 'abc';
$pickupPoint->country = 'DK';
$pickupPoint->name = 'Aalborg Centrum';
```

## `PickupPointAwareInterface`: deprecated id getter, new DTO getter

`PickupPointAwareInterface` (and `PickupPointAwareTrait`) gained a new
`pickup_point` JSON column / `?PickupPoint $pickupPoint` accessor. The
existing `pickupPointId` API stays for backwards compatibility but is now
deprecated:

| Deprecated (1.x / early 2.x)                  | 2.x replacement                                       |
|-----------------------------------------------|-------------------------------------------------------|
| `hasPickupPointId(): bool`                    | `hasPickupPoint(): bool`                              |
| `setPickupPointId(?string $pickupPointId)`    | `setPickupPoint(?\Setono\SyliusPickupPointPlugin\DTO\PickupPoint $pickupPoint)` |
| `getPickupPointId(): ?string`                 | `getPickupPoint(): ?\Setono\SyliusPickupPointPlugin\DTO\PickupPoint`            |
| column `pickup_point_id` (`STRING`)           | column `pickup_point` (`JSON`)                        |

The trait ships **both** columns side-by-side. As of 2.0 the plugin reads
and writes only the new `pickup_point` JSON column: the checkout form
resolves the selected point and stores the full DTO, and the shipment label
template renders `shipment.pickupPoint` directly. The legacy
`pickup_point_id` string column is kept only for backwards compatibility and
is never written anymore. Migrate your data in two moves: (1) backfill the
JSON column from the legacy string, (2) when you're ready, drop the legacy
column and stop using the deprecated methods.

### Example data migration

Doctrine migrations live in your application, not in this plugin. Below
is a Doctrine Migrations class that handles step (1) — it adds the
`pickup_point` JSON column (the trait expects it) and populates it from
the legacy `pickup_point_id` strings, splitting on the `---` delimiter.

The table name follows whatever entity uses `PickupPointAwareTrait` — by
convention `sylius_shipment` for a Shipment customisation. Adjust both
the table name and the SQL dialect to match your setup.

```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260520000000BackfillPickupPointJson extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Backfill pickup_point JSON column from legacy pickup_point_id strings';
    }

    public function up(Schema $schema): void
    {
        // 1. Add the new JSON column (skip this statement if a schema diff
        //    already created it via PickupPointAwareTrait's #[ORM\Column]).
        $this->addSql('ALTER TABLE sylius_shipment ADD pickup_point JSON DEFAULT NULL');

        // 2. Split "<provider>---<id>---<country>" into a JSON object.
        //    MySQL / MariaDB:
        $this->addSql(<<<'SQL'
            UPDATE sylius_shipment
            SET pickup_point = JSON_OBJECT(
                'provider', SUBSTRING_INDEX(pickup_point_id, '---', 1),
                'id',       SUBSTRING_INDEX(SUBSTRING_INDEX(pickup_point_id, '---', 2), '---', -1),
                'country',  SUBSTRING_INDEX(pickup_point_id, '---', -1)
            )
            WHERE pickup_point_id IS NOT NULL
              AND pickup_point IS NULL
        SQL);

        // PostgreSQL equivalent (use one or the other, not both):
        //
        // $this->addSql(<<<'SQL'
        //     UPDATE sylius_shipment
        //     SET pickup_point = jsonb_build_object(
        //         'provider', split_part(pickup_point_id, '---', 1),
        //         'id',       split_part(pickup_point_id, '---', 2),
        //         'country',  split_part(pickup_point_id, '---', 3)
        //     )
        //     WHERE pickup_point_id IS NOT NULL
        //       AND pickup_point IS NULL
        // SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_shipment DROP pickup_point');
    }
}
```

The backfill only fills in `provider`, `id` and `country` — the only data
present in the legacy string. The remaining DTO fields (`name`,
`address`, `zipCode`, `city`, `latitude`, `longitude`) stay `null` until
the next time a fresh pickup point is selected. If you need them
populated for historical orders, run an additional pass that fetches each
shipment through the provider registry and re-stores the full DTO.

Once your reads are switched to `getPickupPoint()`, a follow-up migration
can drop the legacy column:

```php
$this->addSql('ALTER TABLE sylius_shipment DROP pickup_point_id');
```

## Doctrine mappings

The traits `PickupPointAwareTrait` and `PickupPointProviderAwareTrait` now use
PHP 8 attribute mappings (`#[ORM\Column]`) instead of PHPDoc annotations.
Doctrine ORM 3 silently ignores PHPDoc-mapped associations, so any class that
re-declares these columns via PHPDoc on the consumer side must be converted to
attributes.

## Carrier provider bundles

The third-party carrier bundles (`setono/dao-bundle`,
`setono/gls-webservice-bundle`, `setono/post-nord-bundle`) moved from
`require-dev` to `suggest`. Each provider is enabled only when:

1. The matching bundle is installed in your application.
2. The provider is set to `true` in your plugin configuration.

The plugin runtime continues to throw a configuration error if you enable a
provider without the matching bundle.

## Removed providers

The Budbee (`Setono\SyliusPickupPointPlugin\Provider\BudbeeProvider`) and
CoolRunner (`Setono\SyliusPickupPointPlugin\Provider\CoolRunnerProvider`)
providers — along with their service definitions and configuration nodes
— have been removed in 2.0. If you depended on either, switch to one of
the remaining providers (DAO, GLS, PostNord, Faker) or implement your own
`ProviderInterface` and tag it with `setono_sylius_pickup_point.provider`.
Drop these keys from your application configuration:

```yaml
# Remove these — no longer supported
setono_sylius_pickup_point:
    providers:
        budbee: true
        coolrunner: true
```

## Removed dev dependencies

- `phpspec/phpspec`, `phpspec/prophecy-phpunit` (old)
- `psalm/*`, `weirdan/doctrine-psalm-plugin`
- `setono/code-quality-pack`, `setono/sylius-behat-pack`
- `behat/behat`
- `jms/serializer-bundle`, `kriswallsmith/buzz`, `nyholm/psr7`
- `polishsymfonycommunity/symfony-mocker-container`
- `matthiasnoback/symfony-config-test`

All testing now uses PHPUnit + Prophecy via `setono/sylius-plugin: ^2.0`
which bundles PHPStan, PHPUnit, Rector, ECS and CI composite actions.

## Removed translation keys

None — translation keys are unchanged from 1.x.
