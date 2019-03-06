## Sylius Translation plugin

The main goal of this plugin is to enable you to edit translation phrases, without having to manually create or modify translation files on the server. This plugin allows you to change any phrase that is in Silyus in admin panel. All custom translations are saved in the `xliff` format.
![v0.2.0](http://i.piccy.info/i9/5d080684d0be2362a42d8928e4641d94/1551832862/178409/1305830/Screenshot_20190306_023556.png)


In the admin panel view for each locale (enabled in the Sylius) shows how many messages have been translated, how many are left and translation progress.
![v0.2.0](http://i.piccy.info/i9/768bda840331adc4a6eb55192259d2e3/1551832837/156671/1305830/Screenshot_20190306_023610.png)

## Installation

```bash
$ composer require yaroslavche/sylius-translation-plugin
```

Import config
```yaml
# config/config.yml

imports:
    # ...
    - { resource: "@YaroslavcheSyliusTranslationPlugin/Resources/config/services.yml" }
```

Import routing
```yaml
# config/routing.yml

yaroslavche_sylius_translation_plugin:
    resource: "@YaroslavcheSyliusTranslationPlugin/Resources/config/admin_routing.yml"
```

Install assets and clear cache.
```bash
$ bin/console sylius:assets:install
$ bin/console cache:clear
```

see on `/admin/translation/`