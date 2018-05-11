## Installation

    ```bash
    $ composer require yaroslavche/sylius-translation-plugin
    $ yarn install
    $ yarn run gulp
    $ bin/console assets:install web -e dev
    ```
## Test
```bash
$ (cd tests/Application && yarn install)
$ (cd tests/Application && yarn run gulp)
$ (cd tests/Application && bin/console assets:install web -e test)

$ (cd tests/Application && bin/console doctrine:database:create -e test)
$ (cd tests/Application && bin/console doctrine:schema:create -e test)

$ (cd tests/Application && bin/console sylius:fixtures:load -e dev)
$ (cd tests/Application && bin/console server:run -d web -e dev)
```
Go to http://localhost:8000/admin/translation

![v0.0.4](http://i.piccy.info/i9/8bb3ff82ad48ad1fbc2f049322477b5a/1526067792/155065/1243534/11052018_224152.png)
