<?php
declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationService
{

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var RepositoryInterface $localeRepository */
    private $localeRepository;

    /**
     * TranslationService constructor.
     * @param TranslatorInterface $translator
     * @param RepositoryInterface $localeRepository
     */
    public function __construct(TranslatorInterface $translator, RepositoryInterface $localeRepository)
    {
        $this->translator = $translator;
        $this->localeRepository = $localeRepository;
    }


    public function getDefaultLocale()
    {
        return 'en';
    }

    public function getLocales()
    {
        $syliusLocales = $this->localeRepository->findAll();
        $locales = [];
        /** @var Locale $locale */
        foreach ($syliusLocales as $key => $locale) {
            $locales[$locale->getCode()] = $locale->getName();
        }
        return $locales;
    }

    public function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }

    public function getMessageCatalogue(?string $localeCode = null): ?MessageCatalogue
    {
        $messageCatalogue = null;
        $locales = Intl::getLocaleBundle()->getLocales();

        /** if locale not set - get full catalogue */
        if (null === $localeCode) {
            $messageCatalogue = new MessageCatalogue($this->getDefaultLocale());
            $languages = Intl::getLanguageBundle()->getLanguageNames();
            foreach ($languages as $currentLocaleCode => $languageName) {
                $this->translator->setLocale($currentLocaleCode);
                $localeMessageCatalogue = $this->translator->getCatalogue($currentLocaleCode);
                foreach ($localeMessageCatalogue->all() as $domain => $translations) {
                    foreach ($translations as $id => $translation) {
                        $messageCatalogue->set($id, '', $domain);
                    }
                }
                unset($localeMessageCatalogue);
            }
        } else {
            if (in_array($localeCode, $locales)) {
                $messageCatalogue = $this->translator->getCatalogue($localeCode);
            }
        }
        return $messageCatalogue;
    }
}
