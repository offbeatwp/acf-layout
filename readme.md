# ACF Page Builder for OffbeatWP

OffbeatWP has a great builder based on Advanced Custom Fields that helps you to create themes with a minimal footprint. You have 100% control over the output of the builder since everything (even rows and component-containers) are based components.

Install by running this command from the root of your OffbeatWP Theme:

```bash
composer require offbeatwp/acf-layout
```

Next, add the following line to your `config/services.php` file:

```
OffbeatWP\AcfLayout\Service::class,
```

## Installing the "Row" and "Component" Components

Run the following command from somewhere in your Wordpress installation.

```bash
wp acf-layout:install
```

## Enable component for the pagebuilder

Add within the `support` array in the settings method `pagebuilder` like:

```php
public static function settings()
{
    return [
        ...
        'supports'   => ['pagebuilder'],
        ...
    ];
}
```
