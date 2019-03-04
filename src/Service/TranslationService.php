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

    /** @var SyliusLocaleMessageCatalogueService $currentLocaleMessageCatalogue */
    private $currentLocaleMessageCatalogue;

    /** @var Locale[] $locales */
    private $locales;

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
        $this->locales = [$en_US, $uk_UA];
        $this->defaultLocale = $en_US;
        $this->currentLocale = $uk_UA;
        $this->currentLocaleMessageCatalogue = new SyliusLocaleMessageCatalogueService($this->translator, $this->currentLocale);
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
        $translation = null;
        // if locale not null && current locale !== locale then create SyliusLocaleMessageCatalogue otherwise use current

        return $translation ?? $id;
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
        return true;
    }

    /**
     * Add domain in current locale
     *
     * @param string $name
     * @return bool
     */
    public function addDomain(string $name): bool
    {
        return true;
    }

    /**
     * Get total translated messages count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalTranslatedCount(Locale $locale = null): int
    {
        return count($this->currentLocaleMessageCatalogue->getTranslatedMessages());
    }

    /**
     * Get total untranslated messages count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalUntranslatedCount(Locale $locale = null): int
    {
        return count($this->currentLocaleMessageCatalogue->getUntranslatedMessages());
    }

    /**
     * Get total domains count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalDomainCount(Locale $locale = null): int
    {
        return count($this->currentLocaleMessageCatalogue->getDomains());
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

    /**
     * @return SyliusLocaleMessageCatalogueService
     */
    public function getCurrentLocaleMessageCatalogue(): SyliusLocaleMessageCatalogueService
    {
        return $this->currentLocaleMessageCatalogue;
    }
}
