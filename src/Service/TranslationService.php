<?php
declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use DateTime;
use Exception;
use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Symfony\Contracts\Translation\TranslatorInterface;

use function \Safe\sprintf;

class TranslationService
{

    /** @var TranslatorInterface $translator */
    private $translator;

    /** @var RepositoryInterface $localeRepository */
    private $localeRepository;

    /** @var string $kernelRootDir */
    private $kernelRootDir;

    /** @var Filesystem $filesystem */
    private $filesystem;

    /** @var Finder $finder */
    private $finder;

    /**
     * TranslationService constructor.
     * @param TranslatorInterface $translator
     * @param RepositoryInterface $localeRepository
     * @param string $kernelRootDir
     */
    public function __construct(
        TranslatorInterface $translator,
        RepositoryInterface $localeRepository,
        string $kernelRootDir
    )
    {
        $this->translator = $translator;
        $this->localeRepository = $localeRepository;
        $this->kernelRootDir = $kernelRootDir;
        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
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
            if ($localeCode === 'en_US') $localeCode = 'en';
            if (in_array($localeCode, $locales)) {
                $messageCatalogue = $this->translator->getCatalogue($localeCode);
            }
        }
        return $messageCatalogue;
    }

    public function getCustomMessageCatalogue(?string $localeCode = null): ?MessageCatalogue
    {
        $messageCatalogue = null;
        $locales = Intl::getLocaleBundle()->getLocales();
        if (!in_array($localeCode, $locales)) {
            return null;
//            throw new Exception('Invalid locale code');
        }

        $messageCatalogue = new MessageCatalogue($localeCode);
        $customMessagesPath = realpath($this->kernelRootDir . '/translations/');
        if ($this->filesystem->exists($customMessagesPath)) {
            $translationFiles = $this->finder->files()->in($customMessagesPath);
            /** @var \SplFileInfo $translationFile */
            foreach ($translationFiles as $translationFile) {
                list($domain, $translationLocaleCode, $format) = explode('.', $translationFile->getFilename());
                if (strtolower($localeCode) !== strtolower($translationLocaleCode)) {
                    continue;
                }
                $loader = null;
                switch (strtolower($format)) {
                    case 'yml':
                    case 'yaml':
                        $loader = new YamlFileLoader();
                        break;
                    case 'xlf':
                    case 'xliff':
                        $loader = new XliffFileLoader();
                }
                if (null === $loader) {
                    continue;
                }
                $messageCatalogue = $loader->load($translationFile->getRealPath(), $localeCode, $domain);
            }
        }

        return $messageCatalogue;
    }

    public function setMessage(string $localeCode, string $domain, string $id, string $message): bool
    {
        $messageCatalogue = $this->getCustomMessageCatalogue($localeCode);
        $metadata = [];
        if ($messageCatalogue->has($id, $domain)) {
            $messageCatalogue->set($id, $message, $domain);
            $metadata = ['notes' => [
                ['category' => 'state', 'content' => 'updated'],
                ['category' => 'iso8601', 'content' => (new DateTime())->format(DATE_ISO8601)]
            ]];
        } else {
            $messageCatalogue->add([$id => $message], $domain);
            $metadata = ['notes' => [
                ['category' => 'state', 'content' => 'new'],
                ['category' => 'iso8601', 'content' => (new DateTime())->format(DATE_ISO8601)]
            ]];
        }
        $messageCatalogue->setMetadata($id, $metadata, $domain);
        return $this->save($messageCatalogue);
    }

    public function save(MessageCatalogue $messageCatalogue, string $format = 'xliff'): bool
    {
        try {
            $customMessagesPath = realpath($this->kernelRootDir . '/translations/');
            $dumper = new XliffFileDumper();
            $writer = new TranslationWriter();
            $writer->addDumper($format, $dumper);
            $writer->write($messageCatalogue, $format, ['path' => $customMessagesPath]);
            /** @todo warmup translation cache */
            return true;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @param string $localeCode
     * @return Locale|null
     * @throws Exception
     */
    public function addLocale(string $localeCode): ?Locale
    {
        /** @var Locale $locale */
        $locale = $this->localeRepository->findOneBy(['code' => $localeCode]);
        if ($locale instanceof Locale) {
            throw new Exception(sprintf('Locale "%s" already exists', $locale->getName()));
        }
        $locales = Intl::getLocaleBundle()->getLocales();
        if (!in_array($localeCode, $locales)) {
            throw new Exception(sprintf('Locale code "%s" not found', $localeCode));
        }
        $locale = new Locale();
        $locale->setCode($localeCode);
        $this->localeRepository->add($locale);
        return $locale;
    }

    /**
     * @param string $localeCode
     * @return bool|null
     * @throws Exception
     */
    public function removeLocale(string $localeCode): ?bool
    {
        /** @var Locale $locale */
        $locale = $this->localeRepository->findOneBy(['code' => $localeCode]);
        try {
            $this->localeRepository->remove($locale);
            return true;
        } catch (Exception $exception) {
            throw new Exception(sprintf('Failed to remove locale code "%s"', $localeCode));
        }
    }
}
