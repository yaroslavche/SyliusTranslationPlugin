<?php

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Contracts\Translation\TranslatorInterface;

class SyliusLocaleMessageCatalogueService
{
    private $translator;

    /** @var Locale $locale */
    private $locale;

    /** @var MessageCatalogue $messageCatalogue */
    private $messageCatalogue;

    /** @var string[] $domains */
    private $domains;

    /** @var array $translatedMessages */
    private $translatedMessages;

    /** @var array $untranslatedMessages */
    private $untranslatedMessages;

    /** @var array $customMessages */
    private $customMessages;

    /**
     * SyliusLocaleMessageCatalogueService constructor
     *
     * @param Locale|null $locale
     */
    public function __construct($translator, ?Locale $locale = null)
    {
        $this->translator = $translator;
        $this->locale = $locale;
        $this->messageCatalogue = $this->translator->getCatalogue();
        $this->domains = $this->messageCatalogue->getDomains();
    }

    private function loadMessageCatalogue()
    {
//        $messageCatalogue = new MessageCatalogue($this->locale->getCode());
//        $localeMessageCatalogue = null;
//        $languages = Intl::getLanguageBundle()->getLanguageNames();
//        foreach ($languages as $localeCode => $languageName) {
//            $currentLocaleMessageCatalogue = $this->plugin->translator->getCatalogue($localeCode);
//            if ($localeCode === $locale->getCode()) {
//                $localeMessageCatalogue = $currentLocaleMessageCatalogue;
//            }
//            $messages = $currentLocaleMessageCatalogue->all();
//            foreach ($messages as $domain => $translations) {
//                $messageCatalogue->add($translations, $domain);
//            }
//        }
//        $messages = $messageCatalogue->all();
//        foreach ($messages as $domain => $translations) {
//            foreach ($translations as $key => $translation) {
//                $messageCatalogue->set($key, '', $domain);
//            }
//        }
//        if (null !== $localeMessageCatalogue) {
//            $messageCatalogue->addCatalogue($localeMessageCatalogue);
//        }
//        return $messageCatalogue;
    }

    public function getDomains(): array
    {
        return $this->domains;
    }

    public function getTranslatedMessages(?string $domain = null): array
    {
        if(null === $domain) {
            // get all
            return [];
        }
        if(in_array($domain, $this->domains)) return $this->messageCatalogue->all($domain);
        return [];
    }

    public function getUntranslatedMessages(?string $domain = null): array
    {
        return ['message2' => '', 'test_message2' => ''];
    }

    public function getCustomMessages(?string $domain = null): array
    {
        return ['message1' => '3', 'test_message3' => 'test'];
    }

    public function getMessages(): array
    {
        return array_merge($this->getTranslatedMessages() ?? [], $this->getUntranslatedMessages() ?? [], $this->getCustomMessages() ?? []);
    }

    public function setMessage(string $id, string $translation, ?string $domain = 'messages'): bool
    {
        return true;
    }

    public function addDomain(string $name): bool
    {
        return true;
    }

    public function findTranslation(string $id, ?string $domain = null): ?string
    {
        $domain = $domain ?? 'messages'; // ??=

        return $domain . $id;
    }
}
