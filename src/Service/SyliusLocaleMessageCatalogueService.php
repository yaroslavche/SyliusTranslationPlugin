<?php

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

class SyliusLocaleMessageCatalogueService
{
    /** @var DataCollectorTranslator $translationService */
    private $translationService;

    /** @var Locale $locale */
    private $locale;

    /** @var MessageCatalogue $messageCatalogue */
    private $messageCatalogue;

    /** @var MessageCatalogue $customMessageCatalogue */
    private $customMessageCatalogue;

    /** @var MessageCatalogue $fullMessageCatalogue */
    private $fullMessageCatalogue;

    /** @var int $totalMessagesCount */
    private $totalMessagesCount;

    /** @var int $totalTranslatedMessagesCount */
    private $totalTranslatedMessagesCount;

    /**
     * SyliusLocaleMessageCatalogueService constructor
     *
     * @param TranslationService $translationService
     * @param Locale|null $locale
     */
    public function __construct(TranslationService $translationService, ?Locale $locale = null)
    {
        $this->translationService = $translationService;
        $this->locale = $locale;
        $this->fullMessageCatalogue = $this->translationService->getFullMessageCatalogue();

        $this->collectCustomMessageCatalogue();

        $this->messageCatalogue = new MessageCatalogue($this->locale->getCode());
        $localeCode = $this->locale->getCode();
        if($localeCode === 'en_US') $localeCode = 'en';
        $this->copyMessageCatalogue($this->translationService->getTranslator()->getCatalogue($localeCode), $this->messageCatalogue);
        $this->copyMessageCatalogue($this->customMessageCatalogue, $this->messageCatalogue);
    }

    private function copyMessageCatalogue(?MessageCatalogueInterface $source, ?MessageCatalogueInterface $destination)
    {
        if (null === $source || null === $destination) {
            return;
        }
        foreach ($source->all() as $domain => $translations) {
            foreach ($translations as $id => $translation) {
                $destination->set($id, $translation, $domain);
            }
        }
    }

    private function collectCustomMessageCatalogue()
    {
        dump('implement ' . __METHOD__);
        $this->customMessageCatalogue = new MessageCatalogue($this->locale->getCode());
    }

    public function getMessageCatalogue(): MessageCatalogue
    {
        return $this->messageCatalogue;
    }

    public function getCustomMessageCatalogue(): MessageCatalogue
    {
        return $this->customMessageCatalogue;
    }

    public function getFullMessageCatalogue(): MessageCatalogue
    {
        return $this->fullMessageCatalogue;
    }

    /**
     * Get total messages count for locale
     *
     * @param bool $refresh
     * @return int
     */
    public function getTotalMessagesCount(bool $refresh = false): int
    {
        if(null === $this->totalMessagesCount || $refresh)
        {
            $messages = [];
            foreach ($this->fullMessageCatalogue->all() as $domain => $translations) {
                $messages = array_merge($messages, $translations);
            }
            $this->totalMessagesCount = count($messages);
        }

        return $this->totalMessagesCount;
    }

    /**
     * Get total translated messages count in $locale
     *
     * @param bool $refresh
     * @return int
     */
    public function getTotalTranslatedMessagesCount(bool $refresh = false): int
    {
        if(null === $this->totalTranslatedMessagesCount || $refresh)
        {
            $messages = [];
            foreach ($this->messageCatalogue->all() as $domain => $translations) {
                $messages = array_merge($messages, $translations);
            }
            $this->totalTranslatedMessagesCount = count($messages);
        }

        return $this->totalTranslatedMessagesCount;
    }

    public function save(): bool
    {
        dump('implement ' . __METHOD__);
        return true;
    }
}
