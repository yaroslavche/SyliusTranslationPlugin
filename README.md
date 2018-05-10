## Installation

    ```bash
    $ composer require yaroslavche/syliustranslationplugin
    $ yarn install
    $ yarn run gulp
    $ bin/console assets:install web -e dev
    ```

## Tests

  - PHPUnit

    ```bash
    $ bin/phpunit
    ```

  - PHPSpec

    ```bash
    $ bin/phpspec run
    ```

  - Behat (non-JS scenarios)

    ```bash
    $ bin/behat --tags="~@javascript"
    ```

  - Behat (JS scenarios)

    1. Download [Chromedriver](https://sites.google.com/a/chromium.org/chromedriver/)

    2. Run Selenium server with previously downloaded Chromedriver:

        ```bash
        $ bin/selenium-server-standalone -Dwebdriver.chrome.driver=chromedriver
        ```
    3. Run test application's webserver on `localhost:8080`:

        ```bash
        $ (cd tests/Application && bin/console server:run 127.0.0.1:8080 -d web -e test)
        ```

    4. Run Behat:

        ```bash
        $ bin/behat --tags="@javascript"
        ```
