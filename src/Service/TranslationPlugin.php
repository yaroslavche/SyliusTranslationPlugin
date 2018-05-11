<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Writer\TranslationWriter;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class TranslationPlugin implements ContainerAwareInterface
{
    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;

    /**
     * @var Locale|null $locale
     */
    private $locale;

    /**
     * @var Locale|null $syliusDefaultLocale
     */
    private $syliusDefaultLocale;

    /**
     * @var Locale[]|null $syliusAvailableLocales
     */
    private $syliusAvailableLocales;

    /**
     * @var TranslatorInterface|null $translator
     */
    private $translator;

    /**
     * @var MessageCatalogue $messageCatalogue
     */
    private $messageCatalogue;

    /**
     * @var MessageCatalogue $customMessageCatalogue
     */
    private $customMessageCatalogue;

    /**
     * @var array $domains
     */
    private $domains;



    /**
     * init plugin
     * check if container is set, trying get sylius locales data and set syliusDefaultLocale as Locale object, not locale code string
     * @return null
     */
    public function init()
    {
        if (is_null($this->container)) {
            throw new \Exception('Container is null!');
        }
        try {
            $syliusDefaultLocaleCode = $this->container->get('sylius.locale_provider')->getDefaultLocaleCode();
            $this->syliusAvailableLocales = $this->container->get('sylius.repository.locale')->findAll();
            $translator = $this->container->get('translator');
            $this->translator = clone $translator;
        } finally {
            if (empty($syliusDefaultLocaleCode)) {
                throw new \Exception('Sylius default locale is empty!');
            }
            if (empty($this->syliusAvailableLocales)) {
                throw new \Exception('Sylius available locales is empty!');
            }
            // if (!$translator instanceof TranslatorBagInterface) {
            //     throw new \Exception(sprintf(
            //         'The Translator "%s" must implement TranslatorInterface and TranslatorBagInterface.',
            //         get_class($translator)
            //     ));
            // }
        }
        foreach ($this->syliusAvailableLocales as $locale) {
            $localeCode = $locale->getCode();
            if ($localeCode === $syliusDefaultLocaleCode) {
                $this->syliusDefaultLocale = $locale;
                break;
            }
        }
    }

    /**
     * @param Locale|null $locale
     */
    public function setLocale(?Locale $locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getSyliusDefaultLocale();
        }
        $this->locale = $locale;
        $this->translator->setLocale($this->locale->getCode());
        $this->messageCatalogue = $this->translator->getCatalogue($this->locale->getCode());

        $this->customMessageCatalogue = $this->loadCustomLocaleMessages();
        // $this->messageCatalogue->replace($this->customMessageCatalogue->all());
    }

    private function loadCustomLocaleMessages()
    {
        $this->customMessageCatalogue = new MessageCatalogue($this->locale->getCode());
        $kernelRootDir = $this->container->getParameter('kernel.root_dir');
        // TODO: '@Acme.../Resources/' ?
        $customMessagesPath = sprintf('%s/Resources/translations', $kernelRootDir);
        $finder = new Finder();
        $filesystem = new Filesystem();
        $format = 'xliff';
        if ($filesystem->exists($customMessagesPath)) {
            $translationFiles = $finder->files()->name('/[a-z]+\.[a-z]{2}\.[a-z]+/')->in($customMessagesPath);
            $customMessageCatalogue = null;
            foreach ($translationFiles as $translationFile) {
                list($domain, $localeCode) = explode('.', $translationFile->getFilename());
                if ($localeCode !== $this->locale->getCode()) {
                    continue;
                }

                $loaderServiceAlias = sprintf('translation.loader.%s', $format);
                if (!$this->container->has($loaderServiceAlias)) {
                    throw new \RuntimeException(sprintf('Unable to find Symfony Translation loader for format "%s"', $format));
                }
                $loader = $this->container->get($loaderServiceAlias);
                $customMessageCatalogue = $loader->load($translationFile->getRealPath(), $localeCode, $domain);
                if (null !== $customMessageCatalogue) {
                    $this->customMessageCatalogue->addCatalogue($customMessageCatalogue);
                    $customMessageCatalogue = null;
                }
            }
        }
        return $this->customMessageCatalogue;
    }

    /**
     * @param string|null $localeCode
     */
    public function setLocaleByCode(?string $localeCode = null)
    {
        if (is_null($localeCode)) {
            $this->setLocale();
        } else {
            $locale = $this->container->get('sylius.repository.locale')->findOneBy(['code' => $localeCode]);
            if ($locale instanceof Locale) {
                $this->setLocale($locale);
            } else {
                $availableLocales = $this->getSyliusAvailableLocales();
                $localeCodesList = [];
                if (!is_null($availableLocales)) {
                    foreach ($availableLocales as $locale) {
                        $localeCodesList[] = sprintf('"%s"', $locale->getCode());
                    }
                }
                throw new \Exception(sprintf('Unsupported locale "%s". Available locales: %s', $localeCode, implode(', ', $localeCodesList)));
            }
        }
    }

    public function setMessage(string $messageDomain, ?string $message = null)
    {
        // TODO: __call. if method exists - check if locale is set. excluding some methods
        if (null === $this->locale) {
            throw new \Exception('Use method "setLocale($localeCode = null)" before.');
        }
        $domain = null;
        // TODO: check $messageDomain?
        $messageDomains = explode('.', $messageDomain);
        $domains = $this->getDomains();
        if (count($messageDomains) > 1 && in_array($messageDomains[0], $domains)) {
            $domain = $messageDomains[0];
            $messageDomain = implode('.', array_slice($messageDomains, 1));
        }
        return $this->setDomainMessage($domain, $messageDomain, $message);
    }

    public function setDomainMessage(?string $domain = null, string $messageDomain, ?string $translation = null)
    {
        if (empty($messageDomain)) {
            throw new \Exception('Message domain must be not empty.');
        }
        $translation = $translation ?? '';

        if ($this->customMessageCatalogue->has($messageDomain, $domain)) {
            $this->customMessageCatalogue->set($messageDomain, $translation, $domain);
        // TODO: getMetadata and update
            // $this->customMessageCatalogue->setMetadata($messageDomain, ['notes' => [
            //     ['category' => 'state', 'content' => 'new'],
            //     ['category' => 'approved', 'content' => 'false'],
            //     ['category' => 'section', 'content' => $messageDomain, 'priority' => '1']
            // ]]);
        } else {
            $this->customMessageCatalogue->add([$messageDomain => $translation], $domain);
            $this->customMessageCatalogue->setMetadata($messageDomain, ['notes' => [
                ['category' => 'state', 'content' => 'new'],
                ['category' => 'approved', 'content' => 'false'],
                ['category' => 'section', 'content' => $domain ?? 'messages', 'priority' => '1']
            ]], $domain);
        }
    }

    public function writeTranslations(?string $path = null, ?string $format = 'xliff')
    {
        $kernelRootDir = $this->container->getParameter('kernel.root_dir');
        // TODO: hardcoded.
        // TODO: need fix for flex
        // TODO: create path if not exists. filesystem mkdir
        $translationsPath = $kernelRootDir . '/Resources/translations';

        $dumperServiceAlias = sprintf('translation.dumper.%s', $format);
        if (!$this->container->has($dumperServiceAlias)) {
            throw new \InvalidArgumentException(sprintf('Unable to find Symfony Translation dumper for format "%s"', $format));
        }
        $dumper = $this->container->get($dumperServiceAlias);
        $dumper->setBackup(false);
        // TODO: static aliases check on init. container->has()
        $writer = $this->container->get('translation.writer');
        $writer->addDumper($format, $dumper);
        // TODO: translations path in config
        $writer->writeTranslations($this->customMessageCatalogue, $format, ['path' => $translationsPath]);

        $translationCacheDir = sprintf('%s/translations', $this->container->getParameter('kernel.cache_dir'));
        $finder = new Finder();
        // TODO: filesystem create in private getter if null
        $filesystem = new Filesystem();
        $filesystem->mkdir($translationCacheDir);
        $files = $finder->files()->name('*.' . $this->locale->getCode() . '.*')->in($translationCacheDir);
        foreach ($files as $file) {
            $filesystem->remove($file);
        }
        if ($this->translator instanceof WarmableInterface) {
            $this->translator->warmUp($translationCacheDir);
        }
    }

    /**
     * get current locale
     * @return Locale|null $locale
     */
    public function getLocale() : ?Locale
    {
        return $this->locale;
    }

    /**
     * @return Locale|null $syliusDefaultLocale
     */
    public function getSyliusDefaultLocale() : ?Locale
    {
        return $this->syliusDefaultLocale;
    }

    /**
     * @return Locale[] $syliusAvailableLocales
     */
    public function getSyliusAvailableLocales()
    {
        return $this->syliusAvailableLocales;
    }

    public function getMessages(?string $domain = null) : array
    {
        $catalogue = $this->messageCatalogue;
        $messages = $catalogue->all($domain);
        // while ($catalogue = $catalogue->getFallbackCatalogue()) {
        //     $messages = array_replace_recursive($catalogue->all($domain), $messages);
        // }
        return $messages;
    }

    public function getCustomMessages(?string $domain = null)
    {
        $messages = $this->customMessageCatalogue->all($domain);
        return $messages;
    }

    public function getDomains()
    {
        return $this->messageCatalogue->getDomains();
    }

    public function checkTranslations()
    {
        $checker = new TranslationChecker($this);
        $checker->check();
    }
}
