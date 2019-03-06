<?php
declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Locale\Provider\LocaleProviderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;

class TranslationService
{
    const PLUGIN_TRANSLATION_DOMAIN = 'YaroslavcheSyliusTranslationPlugin';

    /** @var DataCollectorTranslator $translator */
    private $translator;

    /** @var Locale $defaultLocale */
    private $defaultLocale;

    /** @var Locale $currentLocale */
    private $currentLocale;

    /** @var Locale[] $syliusLocales */
    private $syliusLocales;

    /** @var array|SyliusLocaleMessageCatalogueService[$localeCode => $syliusLocaleMessageCatalogue] $syliusLocaleMessageCatalogues */
    private $syliusLocaleMessageCatalogues;

    /** @var MessageCatalogue $fullMessageCatalogue */
    private $fullMessageCatalogue;

    /** @var string $kernelRootDir */
    private $kernelRootDir;

    /** @var string $kernelRootDir */
    private $kernelCacheDir;

    /**
     * TranslationService constructor.
     * @param DataCollectorTranslator $translator
     * @param LocaleProviderInterface $localeProvider
     * @param RepositoryInterface $localeRepository
     * @param string $kernelRootDir
     * @param string $kernelCacheDir
     */
    public function __construct(
        DataCollectorTranslator $translator,
        LocaleProviderInterface $localeProvider,
        RepositoryInterface $localeRepository,
        string $kernelRootDir,
        string $kernelCacheDir
    ) {
        $this->kernelRootDir = $kernelRootDir;
        $this->kernelCacheDir = $kernelCacheDir;
        $this->translator = $translator;
        $this->syliusLocales = $localeRepository->findAll();
        foreach ($this->syliusLocales as $locale) {
            $localeCode = $locale->getCode();
            if ($localeCode === $localeProvider->getDefaultLocaleCode()) {
                $this->defaultLocale = $locale;
                break;
            }
        }
        $this->collectFullMessageCatalogue();
        $this->collectSyliusLocaleMesageCatalogues();
        $this->setCurrentLocale($this->defaultLocale);
    }

    private function collectSyliusLocaleMesageCatalogues(bool $refresh = true)
    {
        if ($refresh) {
            unset($this->syliusLocaleMessageCatalogues);
        }
        foreach ($this->getSyliusLocales() as $key => $locale) {
            if (!$refresh && array_key_exists($locale->getCode(), $this->syliusLocaleMessageCatalogues ?? [])) {
                continue;
            }
            $this->syliusLocaleMessageCatalogues[$locale->getCode()] = new SyliusLocaleMessageCatalogueService($this, $locale);
        }
    }

    /**
     * @param Locale|null $locale
     * @return SyliusLocaleMessageCatalogueService
     */
    public function getSyliusLocaleMessageCatalogue(?Locale $locale = null): SyliusLocaleMessageCatalogueService
    {
        $locale = $locale ?? $this->currentLocale;

        return $this->syliusLocaleMessageCatalogues[$locale->getCode()];
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
     * @param Locale $currentLocale
     */
    public function setCurrentLocale(Locale $currentLocale): void
    {
        $this->currentLocale = $currentLocale;
    }

    /**
     * Get available Sylius locales
     *
     * @return array|Locale[]
     */
    public function getSyliusLocales()
    {
        return $this->syliusLocales;
    }

    /**
     * @return DataCollectorTranslator
     */
    public function getTranslator(): DataCollectorTranslator
    {
        return $this->translator;
    }

    /**
     * Find locale by $localeCode used in Sylius
     *
     * @param string $localeCode
     * @return Locale|null $locale
     */
    public function findLocaleByCode(string $localeCode): ?Locale
    {
        foreach ($this->syliusLocales as $key => $locale) {
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
        $syliusLocaleMessageCatalogue = $this->getSyliusLocaleMessageCatalogue($locale);
        $translation = $syliusLocaleMessageCatalogue->getMessageCatalogue()->get($id, $domain);
//        if(gettype($translation) === 'boolean')
        if (true === $translation) {
            return 'true';
        }
        if (false === $translation) {
            return 'false';
        }

        if (is_string($translation) || is_null($translation)) {
            return $translation;
        }

        return null;
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
        $syliusLocaleMessageCatalogue = $this->getSyliusLocaleMessageCatalogue($locale);
        $messageCatalogue = $syliusLocaleMessageCatalogue->getCustomMessageCatalogue();
        $messageCatalogue->add([$id => $translation], $domain);
        $messageCatalogue->setMetadata($id, ['notes' => [
            ['category' => 'state', 'content' => 'new'],
            ['category' => 'approved', 'content' => 'false'],
            ['category' => 'section', 'content' => $domain, 'priority' => '1']
        ]], $domain);
        $result = $syliusLocaleMessageCatalogue->save();

        return $result;
    }

    /**
     * Add domain in current locale
     *
     * @param string $name
     * @param string|null $id
     * @return bool
     */
    public function addDomain(string $name, ?string $id = null): bool
    {
        $syliusLocaleMessageCatalogue = $this->getSyliusLocaleMessageCatalogue();
        $syliusLocaleMessageCatalogue->getCustomMessageCatalogue()->set($id ?? $name . '_title', '', $name);
        $result = $syliusLocaleMessageCatalogue->save();

        return $result;
    }

    /**
     * Get total messages count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalMessagesCount(Locale $locale = null): int
    {
        $syliusLocaleMessageCatalogue = $this->getSyliusLocaleMessageCatalogue($locale);

        return $syliusLocaleMessageCatalogue->getTotalMessagesCount();
    }

    /**
     * Get total translated messages count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalTranslatedMessagesCount(Locale $locale = null): int
    {
        $syliusLocaleMessageCatalogue = $this->getSyliusLocaleMessageCatalogue($locale);

        return $syliusLocaleMessageCatalogue->getTotalTranslatedMessagesCount();
    }

    /**
     * Get total domains count in $locale
     *
     * @param Locale|null $locale
     * @return int
     */
    public function getTotalDomainCount(Locale $locale = null): int
    {
        $syliusLocaleMessageCatalogue = $this->getSyliusLocaleMessageCatalogue($locale);

        return count($syliusLocaleMessageCatalogue->getFullMessageCatalogue()->getDomains());
    }

    /**
     * Calculate translation progress
     *
     * @param Locale|null $locale
     * @return float
     */
    public function calculateTranslationProgress(Locale $locale): float
    {
        $totalMessagesCount = $this->getTotalMessagesCount($locale);
        $translatedMessagesCount = $this->getTotalTranslatedMessagesCount($locale);

        if ($totalMessagesCount === 0) {
            return 0;
        }

        return ($translatedMessagesCount / $totalMessagesCount) * 100;
    }

    /**
     * Calculate total translation progress
     *
     * @param Locale|null $locale
     * @return float
     */
    public function calculateTotalTranslationProgress(): float
    {
        $localesCount = 0;
        $translationProgressSum = 0;

        foreach ($this->getSyliusLocales() as $key => $locale) {
            $translationProgressSum += $this->calculateTranslationProgress($locale);
            $localesCount++;
        }

        if ($localesCount === 0) {
            return 0;
        }

        return $translationProgressSum / $localesCount;
    }

    private function collectFullMessageCatalogue()
    {
        $this->fullMessageCatalogue = new MessageCatalogue($this->defaultLocale->getCode());
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        foreach ($languages as $localeCode => $languageName) {
            $localeMessageCatalogue = $this->translator->getCatalogue($localeCode);
            if (null === $localeMessageCatalogue || null === $this->fullMessageCatalogue) {
                continue;
            }
            foreach ($localeMessageCatalogue->all() as $domain => $translations) {
                foreach ($translations as $id => $translation) {
                    $this->fullMessageCatalogue->set($id, $translation, $domain);
                }
            }
        }
        foreach ($this->fullMessageCatalogue->all() as $domain => $translations) {
            foreach ($translations as $id => $translation) {
                $this->fullMessageCatalogue->set($id, '', $domain);
            }
        }
    }

    /**
     * @return MessageCatalogue
     */
    public function getFullMessageCatalogue(): MessageCatalogue
    {
        return $this->fullMessageCatalogue;
    }

    /**
     * @return string
     */
    public function getKernelRootDir(): string
    {
        return $this->kernelRootDir;
    }

    /**
     * @return string
     */
    public function getKernelCacheDir(): string
    {
        return $this->kernelCacheDir;
    }
}
