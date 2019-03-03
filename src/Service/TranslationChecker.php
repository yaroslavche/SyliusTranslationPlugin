<?php

declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;

use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Intl\Intl;

class TranslationChecker
{
    private $plugin;

    public function __construct(TranslationService $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getFullMessageCatalogue(?Locale $locale = null) : MessageCatalogue
    {
        if (null === $locale) {
            $locale = $this->plugin->getSyliusDefaultLocale();
        }
        $beforeLocale = $this->plugin->getLocale();
        $messageCatalogue = new MessageCatalogue($locale->getCode());
        $localeMessageCatalogue = null;
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        foreach ($languages as $localeCode => $languageName) {
            $currentLocaleMessageCatalogue = $this->plugin->translator->getCatalogue($localeCode);
            if ($localeCode === $locale->getCode()) {
                $localeMessageCatalogue = $currentLocaleMessageCatalogue;
            }
            $messages = $currentLocaleMessageCatalogue->all();
            foreach ($messages as $domain => $translations) {
                $messageCatalogue->add($translations, $domain);
            }
        }
        $this->plugin->setLocale($beforeLocale);
        $messages = $messageCatalogue->all();
        foreach ($messages as $domain => $translations) {
            foreach ($translations as $key => $translation) {
                $messageCatalogue->set($key, '', $domain);
            }
        }
        if (null !== $localeMessageCatalogue) {
            $messageCatalogue->addCatalogue($localeMessageCatalogue);
        }
        return $messageCatalogue;
    }

    public function getTotalMessagesCount(MessageCatalogue $messageCatalogue) : int
    {
        $count = 0;
        foreach ($messageCatalogue->all() as $domain => $translations) {
            $count += count($translations);
        }
        return $count;
    }

    public function getTranslatedMessagesCount(MessageCatalogue $messageCatalogue) : int
    {
        $count = 0;
        foreach ($messageCatalogue->all() as $domain => $translations) {
            foreach ($translations as $key => $translation) {
                if ($translation !== '') {
                    $count++;
                }
            }
        }
        return $count;
    }
}
