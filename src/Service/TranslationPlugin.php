<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Service;

use Sylius\Component\Locale\Model\Locale;
use Sylius\Bundle\ThemeBundle\Translation\ThemeAwareTranslator;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\Writer\TranslationWriter;

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
     * set locale for translations
     * @param string|null $localeCode
     */
    public function setLocale(?string $localeCode)
    {
        if (is_null($localeCode)) {
            $this->locale = $this->getSyliusDefaultLocale();
        } else {
            $locale = $this->container->get('sylius.repository.locale')->findOneBy(['code' => $localeCode]);
            if ($locale instanceof Locale) {
                $this->locale = $locale;
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
        $this->translator->setLocale($this->locale->getCode());
        $this->messageCatalogue = $this->translator->getCatalogue($this->locale->getCode());

        // get custom translations
        // move to method with $domain and $format
        $format = 'xliff';
        $loaderServiceAlias = sprintf('translation.loader.%s', $format);
        if (!$this->container->has($loaderServiceAlias)) {
            throw new \RuntimeException(sprintf('Unable to find Symfony Translation loader for format "%s"', $format));
        }
        $loader = $this->container->get($loaderServiceAlias);
        $kernelRootDir = $this->container->getParameter('kernel.root_dir');
        $customMessagesFilePath = sprintf(
            '%s/Resources/translations/%s.%s.%s',
            $kernelRootDir,
            $domain ?? 'messages',
            $this->locale->getCode(),
            $format
        );
        $this->customMessageCatalogue = $loader->load($customMessagesFilePath, $this->locale->getCode());
        dump($this->customMessageCatalogue);

        // addMessage
        // $this->messageCatalogue->add([
        //     'original-content' => 'translated-content',
        // ]);
        // $this->messageCatalogue->setMetadata('original-content', ['notes' => [
        //     ['category' => 'state', 'content' => 'new'],
        //     ['category' => 'approved', 'content' => 'true'],
        //     ['category' => 'section', 'content' => 'user login', 'priority' => '1'],
        // ]]);
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
        // TODO: static aliases check on init. container->has()
        $writer = $this->container->get('translation.writer');
        $writer->addDumper($format, $dumper);
        $w = $writer->writeTranslations($this->messageCatalogue, $format, ['path' => $translationsPath]);
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
        while ($catalogue = $catalogue->getFallbackCatalogue()) {
            $messages = array_replace_recursive($catalogue->all($domain), $messages);
        }
        return $messages;
    }

    public function getCustomMessages(?string $domain = null)
    {
        $messages = $this->customMessageCatalogue->all($domain);
        return $messages;
    }
}
