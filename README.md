## Installation

Download and install
```bash
$ composer require yaroslavche/sylius-translation-plugin
$ yarn install
$ yarn run gulp
$ bin/console assets:install web -e dev
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
$ bin/console cache:clear
$ bin/console cache:warmup
$ bin/console server:run
```
Go to http://localhost:8000/admin/translation

![v0.1.0](http://i.piccy.info/i9/a8186e675d18c9f4fdb96ef2bf67f612/1526384369/179348/1243534/15052018_143836.png)
![v0.1.0](http://i.piccy.info/i9/c90f0417438587733e28914ef33f8737/1526384347/174800/1243534/15052018_143822.png)
