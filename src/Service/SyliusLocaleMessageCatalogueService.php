<?php

namespace Yaroslavche\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\Translation\DataCollectorTranslator;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Writer\TranslationWriter;

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

    /** @var Filesystem $filesystem */
    private $filesystem;

    /** @var Finder $finder */
    private $finder;

    /** @var string $customTranslationsFormat */
    private $customTranslationsFormat;

    /**
     * SyliusLocaleMessageCatalogueService constructor
     *
     * @param TranslationService $translationService
     * @param Locale|null $locale
     */
    public function __construct(TranslationService $translationService, ?Locale $locale = null)
    {
        $this->translationService = $translationService;
        $this->locale = $locale ?? $this->translationService->getDefaultLocale();

        $this->filesystem = new Filesystem();
        $this->finder = new Finder();
        $this->customTranslationsFormat = 'xliff';
        $this->collectCustomMessageCatalogue();

        $this->fullMessageCatalogue = $this->translationService->getFullMessageCatalogue();

        $this->messageCatalogue = new MessageCatalogue($this->locale->getCode());
        $localeCode = $this->locale->getCode();
        if ($localeCode === 'en_US') {
            $localeCode = 'en';
        }
//        $this->copyMessageCatalogue($this->fullMessageCatalogue, $this->messageCatalogue);
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
        $this->customMessageCatalogue = new MessageCatalogue($this->locale->getCode());

        $customMessagesPath = realpath($this->translationService->getKernelRootDir() . '/../translations/');

        if ($this->filesystem->exists($customMessagesPath)) {
            $translationFiles = $this->finder->files()->in($customMessagesPath);
            /** @var \SplFileInfo $translationFile */
            foreach ($translationFiles as $translationFile) {
                list($domain, $localeCode, $format) = explode('.', $translationFile->getFilename());
                if (strtolower($localeCode) !== strtolower($this->locale->getCode())) {
                    continue;
                }

                $loader = null;
                switch (strtolower($format)) {
                    case 'yml':
                    case 'yaml':
                        $loader = new YamlFileLoader();
                        break;
                    case 'xliff':
                        $loader = new XliffFileLoader();
                }
                if (null === $loader) {
                    continue;
                }

                $this->customMessageCatalogue = $loader->load($translationFile->getRealPath(), $localeCode, $domain);
            }
        }
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
        if (null === $this->totalMessagesCount || $refresh) {
            $this->totalMessagesCount = 0;
            foreach ($this->fullMessageCatalogue->all() as $domain => $translations) {
                $this->totalMessagesCount += count($translations);
            }
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
        if (null === $this->totalTranslatedMessagesCount || $refresh) {
            $this->totalTranslatedMessagesCount = 0;
            foreach ($this->messageCatalogue->all() as $domain => $translations) {
                $this->totalTranslatedMessagesCount += count($translations);
            }
        }

        return $this->totalTranslatedMessagesCount;
    }

    public function save(MessageCatalogue $messageCatalogue): bool
    {
        try {
            $customMessagesPath = realpath($this->translationService->getKernelRootDir() . '/../translations/');

            $dumper = new XliffFileDumper();
            $writer = new TranslationWriter();
            $writer->addDumper($this->customTranslationsFormat, $dumper);
            $writer->write($messageCatalogue, $this->customTranslationsFormat, ['path' => $customMessagesPath]);

            $translationCacheDir = sprintf('%s/translations', $this->translationService->getKernelCacheDir());
            if (!$this->filesystem->exists($translationCacheDir)) {
                /** @todo maybe not need */
                $this->filesystem->mkdir($translationCacheDir);
                $this->warmUpTranslationsCache($translationCacheDir);
                return true;
            }
            $files = $this->finder->files()->name('*.' . $this->locale->getCode() . '.*')->in($translationCacheDir);
            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                $this->filesystem->remove($file->getRealPath());
            }
            $this->warmUpTranslationsCache($translationCacheDir);
            return true;
        } catch (\Exception $exception) {
            dump($exception);
            return false;
        }
    }

    private function warmUpTranslationsCache(string $translationCacheDir)
    {
        if ($this->translationService->getTranslator() instanceof WarmableInterface) {
            /** @var WarmableInterface $translator */
            $translator = $this->translationService->getTranslator();
            $translator->warmUp($translationCacheDir);
        }
    }
}
