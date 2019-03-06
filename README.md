## Sylius Translation plugin

The main goal of this plugin is to enable you to edit translation phrases, without having to manually create or modify translation files on the server. This plugin allows you to change any phrase that is in Silyus in admin panel. All custom translations are saved in the `xliff` format.
![v0.2.0](http://i.piccy.info/i9/68769484bd712a8201b589d7e3583667/1551895248/166468/1305830/Screenshot_20190306_195601.png)


In the admin panel view for each locale (enabled in the Sylius) shows how many messages have been translated, how many are left and translation progress.
![v0.2.0](http://i.piccy.info/i9/d48f5c7b2108cc2f27f7aa299c539139/1551895214/152581/1305830/Screenshot_20190306_195449.png)

## Installation

```bash
$ composer require yaroslavche/sylius-translation-plugin
```

Register bundle:
```php
# config/bundles.php

Yaroslavche\SyliusTranslationPlugin\YaroslavcheSyliusTranslationPlugin::class => ['all' => true],
```

Import services:
```yaml
# config/services.yaml

imports:
    # ...
    - { resource: "@YaroslavcheSyliusTranslationPlugin/Resources/config/services.yml" }
```

Import routing
```yaml
# config/routes.yaml

yaroslavche_sylius_translation_plugin:
    resource: "@YaroslavcheSyliusTranslationPlugin/Resources/config/admin_routing.yml"
```

Install assets and clear cache.
```bash
$ yarn build
$ bin/console assets:install
$ bin/console cache:clear
```

see on `/admin/translation/`
