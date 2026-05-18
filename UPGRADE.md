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
endpoints now return a `JsonResponse` produced by `Symfony\Component\Serializer\SerializerInterface`,
and serialization groups (`Detailed`, `Autocomplete`) are declared via
`#[Groups]` PHP attributes on `Setono\SyliusPickupPointPlugin\Model\PickupPoint`.

## Plugin file layout

The plugin moved from `src/Resources/**` to repo-root locations
(matches Sylius 2.x conventions / `setono/sylius-plugin-skeleton`):

| 1.x path                                     | 2.0 path                |
|----------------------------------------------|-------------------------|
| `src/Resources/config/`                      | `config/`               |
| `src/Resources/config/services/*.xml`        | `config/services/*.php` (PHP DSL) |
| `src/Resources/config/services/providers/*.xml` | `config/services/providers/*.php` |
| `src/Resources/config/routing.yaml`          | `config/routes/shop.yaml` |
| `src/Resources/config/routing_non_localized.yaml` | `config/routes/shop_non_localized.yaml` |
| `src/Resources/config/doctrine/`             | (removed — no plugin-owned doctrine resource) |
| `src/Resources/config/validation/`           | `config/validation/`    |
| `src/Resources/config/routes/`               | `config/routes/`        |
| `src/Resources/config/app/config.yaml`       | (removed — inlined via `Extension::prepend()`) |
| `src/Resources/config/app/fixtures.yaml`     | (removed — example data, copy into your test app if needed) |
| `src/Resources/config/serializer/PickupPoint.yml` | (removed — replaced by `#[Groups]` attributes on the model) |
| `src/Resources/translations/`                | `translations/`         |
| `src/Resources/views/`                       | `templates/`            |
| `src/Resources/public/`                      | `public/`               |

Update any `@SetonoSyliusPickupPointPlugin/Resources/...` references in your
own templates and config to drop the `Resources/` segment.

## Routing import

```yaml
# config/routes/setono_sylius_pickup_point.yaml

setono_sylius_pickup_point_plugin:
    resource: "@SetonoSyliusPickupPointPlugin/config/routes/shop.yaml"
```

Previously: `@SetonoSyliusPickupPointPlugin/Resources/config/routing.yaml`.

## Templates → Twig hooks

The plugin now wires its layout JS snippet and the pickup-point shipment label
automatically via `sylius_twig_hooks`. Consumers no longer need to:

- include `@SetonoSyliusPickupPointPlugin/_javascripts.html.twig` manually in
  `layout.html.twig`; the plugin attaches it to `sylius_admin.base#javascripts`
  and `sylius_shop.base#javascripts`.
- include `@SetonoSyliusPickupPointPlugin/Shop/Label/Shipment/pickupPoint.html.twig`
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
| `setono_sylius_pickup_point.controller.action.pickup_point_by_id`   | `Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointByIdAction`     |
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

`Setono\SyliusPickupPointPlugin\Model\PickupPointInterface` no longer extends
`Sylius\Component\Resource\Model\ResourceInterface` — `PickupPoint` is now a
plain DTO populated from a carrier API response, not a Doctrine entity.
Consumers that relied on `$pickupPoint->getId()` should drop those calls.

## Doctrine mappings

The traits `PickupPointAwareTrait` and `PickupPointProviderAwareTrait` now use
PHP 8 attribute mappings (`#[ORM\Column]`) instead of PHPDoc annotations.
Doctrine ORM 3 silently ignores PHPDoc-mapped associations, so any class that
re-declares these columns via PHPDoc on the consumer side must be converted to
attributes.

## Carrier provider bundles

The third-party carrier bundles (`setono/budbee-bundle`, `setono/coolrunner-bundle`,
`setono/dao-bundle`, `setono/gls-webservice-bundle`, `setono/post-nord-bundle`)
moved from `require-dev` to `suggest`. Each provider is enabled only when:

1. The matching bundle is installed in your application.
2. The provider is set to `true` in your plugin configuration.

The plugin runtime continues to throw a configuration error if you enable a
provider without the matching bundle.

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
