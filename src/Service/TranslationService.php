<?php
declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;
use Symfony\Component\Translation\DataCollectorTranslator;

class TranslationService
{
    const PLUGIN_TRANSLATION_DOMAIN = 'YaroslavcheSyliusTranslationPlugin';

    /** @var DataCollectorTranslator $translator */
    private $translator;

    /** @var Locale $defaultLocale */
    private $defaultLocale;

    /** @var Locale $currentLocale */
    private $currentLocale;

    /** @var Locale[] $locales */
    private $locales;

    /** @var array|SyliusLocaleMessageCatalogueService[$localeCode => $localeMessageCatalogue] $localeMessageCatalogues */
    private $localeMessageCatalogues;

    /**
     * TranslationService constructor.
     * @param DataCollectorTranslator $translator
     */
    public function __construct(DataCollectorTranslator $translator)
    {
        $this->translator = clone $translator;
        dump($translator->getCatalogue()->all());
        $en_US = new Locale();
        $en_US->setCode('uk_UA');
        $uk_UA = new Locale();
        $uk_UA->setCode('en_US');
        $ru_RU = new Locale();
        $ru_RU->setCode('ru_RU');
        $this->locales = [$en_US, $uk_UA, $ru_RU];
        $this->defaultLocale = $en_US;
        $this->currentLocale = $uk_UA;
        $this->localeMessageCatalogues = [];
        $this->getLocaleMessageCatalogue($this->currentLocale);
    }

    /**
     * @param Locale|null $locale
     * @return SyliusLocaleMessageCatalogueService
     */
    public function getLocaleMessageCatalogue(?Locale $locale = null): SyliusLocaleMessageCatalogueService
    {
        $locale = $locale ?? $this->currentLocale;
        if (!array_key_exists($locale->getCode(), $this->localeMessageCatalogues)) {
            $this->localeMessageCatalogues[$locale->getCode()] = new SyliusLocaleMessageCatalogueService($this->translator, $locale);
        }

        return $this->localeMessageCatalogues[$locale->getCode()];
    }

    /**
     * Get current locale used in Sylius
     *
     * @return Locale $locale
     */
    public function getDefaultLocale(): Locale
    {
        return $this->defaultLocale;
    }

    /**
     * Get current locale selected in plugin
     *
     * @return Locale $locale
     */
    public function getCurrentLocale(): Locale
    {
        return $this->currentLocale;
    }

    /**
     * Get available Sylius locales
     *
     * @return array|Locale[]
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * Find locale by $localeCode used in Sylius
     *
     * @param string $localeCode
     * @return Locale|null $locale
     */
    public function findLocaleByCode(string $localeCode): ?Locale
    {
        foreach ($this->locales as $key => $locale) {
            if ($locale->getCode() === $localeCode) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Find $translation for $locale (default - current) message $id in $domain
     *
     * @param string $id
     * @param string $domain
     * @param Locale $locale
     * @return string|null $translation
     */
    public function findTranslation(string $id, string $domain = 'messages', ?Locale $locale = null): ?string
    {
        $localeMessageCatalogue = $this->getLocaleMessageCatalogue($locale);

        return $localeMessageCatalogue->findTranslation($id, $domain) ?? $id;
    }

    /**
     * Set $translation for $locale message $id in $domain
     *
     * @param Locale $locale
     * @param string $id
     * @param string $translation
     * @param string|null $domain
     * @return bool
     */
    public function setMessage(Locale $locale, string $id, string $translation, ?string $domain = 'messages'): bool
    {
        $localeMessageCatalogue = $this->getLocaleMessageCatalogue($locale);
        return $localeMessageCatalogue->setMessage($id, $translation, $domain);
    }

    /**
     * Add domain in current locale
     *
     * @param string $name
     * @return bool
     */
    public function addDomain(string $name): bool
    {
        $localeMessageCatalogue = $this->getLocaleMessageCatalogue();
        return $localeMessageCatalogue->addDomain($name);
    }

    /**
     * Get total translated messages count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalTranslatedCount(Locale $locale = null): int
    {
        $localeMessageCatalogue = $this->getLocaleMessageCatalogue($locale);
        return count($localeMessageCatalogue->getTranslatedMessages());
    }

    /**
     * Get total untranslated messages count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalUntranslatedCount(Locale $locale = null): int
    {
        $localeMessageCatalogue = $this->getLocaleMessageCatalogue($locale);
        return count($localeMessageCatalogue->getUntranslatedMessages());
    }

    /**
     * Get total domains count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalDomainCount(Locale $locale = null): int
    {
        $localeMessageCatalogue = $this->getLocaleMessageCatalogue($locale);
        return count($localeMessageCatalogue->getDomains());
    }

    /**
     * Calculate translation progress
     *
     * @param Locale|null $locale
     * @return float
     */
    public function calculateTranslationProgress(Locale $locale = null): float
    {
        $translatedCount = $this->getTotalTranslatedCount($locale);
        $untranslatedCount = $this->getTotalUntranslatedCount($locale);
        $totalCount = $translatedCount + $untranslatedCount;
        if ($totalCount === 0) {
            return 0;
        }
        return ($translatedCount / $totalCount) * 100;
    }
}
