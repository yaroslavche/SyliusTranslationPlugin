<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Service;

use Acme\SyliusTranslationPlugin\Service\TranslationPlugin;

class TranslationChecker
{
    private $plugin;

    public function __construct(TranslationPlugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function check()
    {
        $catalogues = [];
        $beforeLocale = $this->plugin->getLocale();
        foreach ($this->plugin->getSyliusAvailableLocales() as $locale) {
            $this->plugin->setLocale($locale);
            $catalogues[$locale->getCode()] = [
                'main' => $this->plugin->getMessages(),
                'custom' => $this->plugin->getCustomMessages()
            ];
        }
        $this->plugin->setLocale($beforeLocale);
        dump($catalogues);
        die();
    }
}
