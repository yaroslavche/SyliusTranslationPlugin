## Installation

Download and install
```bash
$ composer require "yaroslavche/sylius-translation-plugin @dev"
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

![v0.0.6](http://i.piccy.info/i9/4016f96c314c42f157208fd622dd9f07/1526258690/187578/1243534/14052018_034321.png)
![v0.0.6](http://i.piccy.info/i9/02818ed3ad7d16ab361eed543d658347/1526258719/139080/1243534/14052018_034412.png)
