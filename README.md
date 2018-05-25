## Sylius Translation plugin

The main goal of this plugin is to enable you to edit translation phrases, without having to manually create or modify translation files on the server. This plugin allows you to change any phrase that is in Silyus in admin panel. All custom translations are saved in the `xliff` format.
![v0.1.0](http://i.piccy.info/i9/c90f0417438587733e28914ef33f8737/1526384347/174800/1243534/15052018_143822.png)


In the admin panel view for each locale (enabled in the Sylius) shows how many messages have been translated, how many are left and translation progress.
![v0.1.0](http://i.piccy.info/i9/a8186e675d18c9f4fdb96ef2bf67f612/1526384369/179348/1243534/15052018_143836.png)

@dev:
Adding your own domains and messages. If you need custom `blog` translation domain with `my_message` key - you can do it more easily. Just add to any locale and plugin show this phrase for other locales too (plugin collect all messages for all `Intl` languages as `fullMessageCatalogue`, even if it's not defined for selected locale).

Planning: filter, search.

## Installation

Download
```bash
$ composer require yaroslavche/sylius-translation-plugin
```

register bundle
```php
# app/AppKernel.php

// ...
public function registerBundles(): array
{
    return array_merge(parent::registerBundles(), [
        // ...
        new \Acme\SyliusTranslationPlugin\AcmeSyliusTranslationPlugin(),
    ]);
}
```
import services
```yaml
# app/config/config.yml

imports:
    # ...
    - { resource: "@AcmeSyliusTranslationPlugin/Resources/config/services.yml" }
```

add routing
```yaml
# app/config/routing.yml

acme_sylius_translation_plugin:
    resource: "@AcmeSyliusTranslationPlugin/Resources/config/app/routing.yml"
```

and finally
```bash
$ bin/console assets:install web -e dev
$ bin/console cache:clear
$ bin/console cache:warmup
$ bin/console server:run
```
Go to http://localhost:8000/admin/translation
