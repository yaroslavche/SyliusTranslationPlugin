<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Service;

use Acme\SyliusTranslationPlugin\Service\TranslationPlugin;

class LocaleTranslator
{
    private $plugin;

    public function __construct(TranslationPlugin $plugin)
    {
        $this->plugin = $plugin;
        $syliusDefaultLocale = $this->plugin->getSyliusDefaultLocale();
        dump($syliusDefaultLocale);
    }
}
