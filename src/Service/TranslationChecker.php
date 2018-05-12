<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Service;

use Acme\SyliusTranslationPlugin\Service\TranslationPlugin;

class TranslationChecker
{
    private $plugin;
    private $domains;
    private $domainMessages;

    public function __construct(TranslationPlugin $plugin)
    {
        $this->plugin = $plugin;
        $this->domains = [];
        $this->domainMessages = [];
    }

    public function check()
    {
        // $stat = [
        //     'count' => count($availableLocales),
        //     'default' => '',
        //     'defined' => [
        //         'domains' => [],
        //         'messageDomains' => []
        //     ],
        //     'missed' => [
        //         // 'en_US' => ['messages' => ['asd'], 'validators' => ['qwe', 'zxc']]
        //     ]
        // ];
        $catalogues = [];

        $beforeLocale = $this->plugin->getLocale();
        $availableLocales = $this->plugin->getSyliusAvailableLocales();
        foreach ($availableLocales as $locale) {
            $this->plugin->setLocale($locale);
            $messageCatalogue = $this->plugin->getMessageCatalogue();
            $customMessageCatalogue = $this->plugin->getCustomMessageCatalogue();
            $catalogues[$locale->getCode()] = [
                'main' => $messageCatalogue,
                'custom' => $customMessageCatalogue
            ];
            $this->domains = array_unique(array_merge($this->domains, $messageCatalogue->getDomains(), $customMessageCatalogue->getDomains()));
            $messages = $messageCatalogue->all();
            $customMessages = $customMessageCatalogue->all();
            $allMessages = array_merge($messages, $customMessages);
            foreach ($allMessages as $domain => $domainMessages) {
                $this->domainMessages[$domain] = array_keys($domainMessages);
            }
        }
        $this->plugin->setLocale($beforeLocale);
        dump($this->domainMessages);
        die();
    }
}
