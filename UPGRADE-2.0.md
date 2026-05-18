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
| `behat/transliterator` | `symfony/string` (`AsciiSlugger`) |

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
| `src/Resources/config/doctrine/`             | `config/doctrine/`      |
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
| `setono_sylius_pickup_point.command.load_pickup_points`             | `Setono\SyliusPickupPointPlugin\Command\LoadPickupPointsCommand`             |
| `setono_sylius_pickup_point.controller.action.pickup_point_by_id`   | `Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointByIdAction`     |
| `setono_sylius_pickup_point.controller.action.pickup_points_search_by_cart_address` | `Setono\SyliusPickupPointPlugin\Controller\Action\PickupPointsSearchByCartAddressAction` |
| `setono_sylius_pickup_point.message.handler.load_pickup_points`     | `Setono\SyliusPickupPointPlugin\Message\Handler\LoadPickupPointsHandler`     |
| `setono_sylius_pickup_point.validator.has_pickup_point_selected`    | `Setono\SyliusPickupPointPlugin\Validator\Constraints\HasPickupPointSelectedValidator` |
| `setono_sylius_pickup_point.fixture.shipping_method`                | `Setono\SyliusPickupPointPlugin\Fixture\ShippingMethodFixture`               |
| `setono_sylius_pickup_point.fixture.example_factory.shipping_method`| `Setono\SyliusPickupPointPlugin\Fixture\Factory\ShippingMethodExampleFactory`|
| `setono_sylius_pickup_point.shipping.order_shipping_method_selection_requirement_checker` | `Setono\SyliusPickupPointPlugin\Shipping\OrderShippingMethodSelectionRequirementChecker` |
| `setono_sylius_pickup_point.block_event_listener.javascript`        | (removed — JS layout snippet now wired through `sylius_twig_hooks`)         |

These IDs are still kept (compiler pass and bundle config reference them):

- `setono_sylius_pickup_point.registry.provider`
- `setono_sylius_pickup_point.cache` (alias)
- `setono_sylius_pickup_point.provider.*` (per-provider services tagged `setono_sylius_pickup_point.provider`)
- Resource-bundle-managed IDs (`setono_sylius_pickup_point.repository.pickup_point`, `factory.*`, `manager.*`)

## `LoadPickupPointsHandler` signature change

The handler now takes a `Doctrine\Persistence\ManagerRegistry` plus the
pickup-point model class-string, replacing the previously injected
`EntityManagerInterface`. It uses `Setono\Doctrine\ORMTrait` to resolve the
manager lazily and supports multi-manager setups.

```php
public function __construct(
    ServiceRegistryInterface $providerRegistry,
    PickupPointRepositoryInterface $pickupPointRepository,
    ManagerRegistry $managerRegistry,
    string $pickupPointClass,
)
```

Plus the handler is now marked with `#[AsMessageHandler]` and no longer
implements `MessageHandlerInterface`.

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
