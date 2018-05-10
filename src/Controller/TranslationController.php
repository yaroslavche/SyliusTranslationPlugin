<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Controller;

use Acme\SyliusTranslationPlugin\Service\LocaleTranslator;

use Sylius\Component\Locale\Model\Locale;
use Sylius\Component\Locale\Model\Channel;
use Sylius\Component\Core\Dashboard\DashboardStatisticsProvider;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\Translator;

// TODO: move some functions in service
final class TranslationController extends Controller
{
    private $syliusDefaultLocale;
    private $syliusAvailableLocales;
    private $selectedLocale;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * @param string|null $localeCode
     *
     * @return Response
     */
    public function translationAction(?string $localeCode): Response
    {
        $translationPlugin = $this->get('translation_plugin_service');
        $translationPlugin->setLocale($localeCode);
        $translationPlugin->writeTranslations();
        // $faker = \Faker\Factory::create($this->selectedLocale->getCode());
        // for ($entries = 0; $entries < 30; $entries++) {
        //     $this->addMessage(sprintf('namespace_%d.subnamespace_%d', $faker->randomDigitNotNull(), $faker->randomDigitNotNull()), $faker->sentence(), $this->selectedLocale);
        // }
        return $this->render('@AcmeSyliusTranslationPlugin/translation.html.twig', [
            // 'plugin' => $translationPlugin ??
            'locale' => $translationPlugin->getLocale(),
            'messages' => $translationPlugin->getMessages(),
            'locales' => $translationPlugin->getSyliusAvailableLocales(),
        ]);
    }

    private function getSyliusDefaultLocale()
    {
        if (is_null($this->syliusDefaultLocale)) {
            $syliusDefaultLocaleCode = $this->get('sylius.locale_provider')->getDefaultLocaleCode();
            if (is_null($this->syliusAvailableLocales)) {
                $this->getSyliusAvailableLocales();
            }
            foreach ($this->syliusAvailableLocales as $locale) {
                $localeCode = $locale->getCode();
                if ($localeCode === $syliusDefaultLocaleCode) {
                    $this->syliusDefaultLocale = $locale;
                    break;
                }
            }
        }
        return $this->syliusDefaultLocale;
    }

    private function getSyliusAvailableLocales()
    {
        if (is_null($this->syliusAvailableLocales)) {
            $this->syliusAvailableLocales = $this->get('sylius.repository.locale')->findAll();
            if ($this->syliusDefaultLocale === null) {
                $this->getSyliusDefaultLocale();
            }
        }
        return $this->syliusAvailableLocales;
    }

    private function getMessages(Locale $locale)
    {
        $kernelRootDir = $this->container->getParameter('kernel.root_dir');
        $srcPath = realpath($kernelRootDir . '/../src');

        $customMessagesFilePath = sprintf('%s/Resources/translations/messages.%s.yml', $kernelRootDir, $locale->getCode());
        // only for domains array
        // TODO: findDomain(string $text, ?string $localeCode)
        // $srcMessages = sprintf('%s/Resources/translations/messages.%s.yml', $kernelRootDir, $localeCode);
        // $syliusMessages = sprintf('%s/Resources/translations/messages.%s.yml', $kernelRootDir, $localeCode);

        // TODO: user permissions
        $canCreate = true;
        $localeCustomMessages = $this->readFile($customMessagesFilePath, $canCreate);
        $domainMessages = [];
        if (!is_null($localeCustomMessages)) {
            $domainMessages['custom'] = $this->flattenMessages($localeCustomMessages);
        }
        $localeMessages = $this->getLocaleMessages($locale);
        $domainMessages = array_merge($domainMessages, $localeMessages);
        return $domainMessages;
    }

    private function readFile(string $path, ?bool $createIfNotExists = true)
    {
        $fileExists = $this->filesystem->exists($path);
        if (!$fileExists) {
            if ($createIfNotExists) {
                $this->saveFile($path);
            } else {
                throw new \Exception(sprintf('File "%s" not exists', $path));
            }
        }
        return Yaml::parseFile($path);
    }

    private function saveFile(string $path, ?array $messages = [], ?bool $sort = true)
    {
        if ($sort) {
            ksort($messages);
        }
        $this->filesystem->dumpFile($path, Yaml::dump($messages));
    }

    private function addMessage(string $domain, string $message, Locale $locale)
    {
        if (empty($domain)) {
            return;
            // throw new \Exception('Invalid translation message domain');
        }
        $kernelRootDir = $this->container->getParameter('kernel.root_dir');
        $customMessagesFilePath = sprintf('%s/Resources/translations/messages.%s.yml', $kernelRootDir, $locale->getCode());
        $customMessages = $this->readFile($customMessagesFilePath);
        $domains = explode('.', $domain);
        // TODO: message to array
        $messages = [];
        $this->saveFile($customMessagesFilePath, $messages);
    }
    // TODO: addMessages(array $messages). $domain => message

    private function getLocaleMessages(?Locale $locale)
    {
        $localeCode = $locale !== null ? $locale->getCode() : $this->getSyliusDefaultLocale()->getCode();
        $translator = $this->get('translator');
        $catalogue = $translator->getCatalogue($localeCode);
        $messages = $catalogue->all();
        while ($catalogue = $catalogue->getFallbackCatalogue()) {
            $messages = array_replace_recursive($catalogue->all(), $messages);
        }
        return $messages;
    }

    private function flattenMessages(array $message)
    {
        $ritit = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($message));
        $result = [];
        foreach ($ritit as $leafValue) {
            $keys = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $result[join('.', $keys)] = $leafValue;
        }
        return $result;
    }
}

// TODO: move somewhere
class DomainMessage
{
}
