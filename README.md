# Sylius Pickup Point Plugin

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]

Add a map of pickup points to your pickup point enabled shipping methods.

### Supported providers
- GLS
- PostNord

## Installation


### Step 1: Install and enable plugin

Open a command console, enter your project directory and execute the following command to download the latest stable version of this plugin:

```bash
$ composer require setono/sylius-pickup-point-plugin
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Add the plugin to `bundles.php`.

```php
Setono\SyliusPickupPointPlugin\SetonoSyliusPickupPointPlugin::class => ['all' => true],
```

### Step 2: Import routing

````yaml
setono_sylius_pickup_point_plugin:
    resource: "@SetonoSyliusPickupPointPlugin/Resources/config/routing.yml"
````

### Step 3: Addition of a validation group `checkout_select_shipping`

````yaml
parameters:
    sylius.form.type.checkout_select_shipping.validation_groups: ['sylius', 'checkout_select_shipping']
````

### Step 4: Update templates

Add the following to the admin shipping method form `SyliusAdminBundle/ShippingMethod/_form.html.twig`
````twig
{{ form_row(form.pickupPointProvider) }}
````

and to `SyliusShopBundle/Checkout/SelectShipping/_shipment.html.twig`
````twig
{{ form_row(form.pickupPointId) }}
````

### Step 5: Customize entities

Add `PickupPointIdTrait` and `PickupPointIdAwareInterface` to you `Shipment` entity.

Add `PickupPointProviderTrait` and `PickupPointProviderAwareInterface` to your `ShippingMethod` entity.

Include these fields in your custom entities

````xml
<?xml version="1.0" encoding="UTF-8"?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                      http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

    <mapped-superclass name="App\Entity\Shipment" table="sylius_shipment">
        <field name="pickupPointId" column="pickup_point_id" nullable="true" />
    </mapped-superclass>

</doctrine-mapping>
````

````xml
<?xml version="1.0" encoding="UTF-8"?>

<doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                  xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                                      http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

    <mapped-superclass name="App\Entity\ShippingMethod" table="sylius_shipping_method">
        <field name="pickupPointProvider" column="pickup_point_provider" nullable="true" />
    </mapped-superclass>

</doctrine-mapping>
````

[ico-version]: https://img.shields.io/packagist/v/setono/sylius-pickup-point-plugin.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://travis-ci.com/Setono/SyliusPickupPointPlugin.svg?branch=master
[ico-code-quality]: https://img.shields.io/scrutinizer/g/Setono/SyliusPickupPointPlugin.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/setono/sylius-pickup-point-plugin
[link-travis]: https://travis-ci.com/Setono/SyliusPickupPointPlugin
[link-code-quality]: https://scrutinizer-ci.com/g/Setono/SyliusPickupPointPlugin
