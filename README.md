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

![v0.0.4](http://i.piccy.info/i9/8bb3ff82ad48ad1fbc2f049322477b5a/1526067792/155065/1243534/11052018_224152.png)
