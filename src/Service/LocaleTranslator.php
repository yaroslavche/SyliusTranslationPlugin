<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Service;

use Acme\SyliusTranslationPlugin\Service\TranslationPlugin;

use Sylius\Component\Locale\Model\Locale;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\Translator;

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
