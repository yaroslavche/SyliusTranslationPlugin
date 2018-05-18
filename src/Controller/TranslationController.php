<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TranslationController extends Controller
{
    /**
     * @return Response
     */
    public function dashboardAction(): Response
    {
        $translationPlugin = $this->get('translation_plugin_service');
        $translationPlugin->setLocale();

        return $this->render('@AcmeSyliusTranslationPlugin/dashboard.html.twig', [
            'plugin' => $translationPlugin
        ]);
    }

    /**
     * @param string|null $localeCode
     * @return Response
     */
    public function localeAction(?string $localeCode): Response
    {
        $translationPlugin = $this->get('translation_plugin_service');
        $translationPlugin->setLocaleByCode($localeCode);
        return $this->render('@AcmeSyliusTranslationPlugin/locale.html.twig', [
            'plugin' => $translationPlugin
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function setMessageAction(Request $request) : Response
    {
        $localeCode = $request->request->get('localeCode');
        $domain = $request->request->get('domain');
        $messageDomain = $request->request->get('messageDomain');
        $translation = $request->request->get('translation');
        if (!is_null($localeCode) && !is_null($domain) && !is_null($messageDomain) && !is_null($translation)) {
            $translationPlugin = $this->get('translation_plugin_service');
            $translationPlugin->setLocaleByCode($localeCode);
            $translationPlugin->setDomainMessage($domain, $messageDomain, $translation);
            $translationPlugin->writeTranslations();
            return new Response(json_encode(['status' => 'success']));
        }
        return new Response(json_encode(['status' => 'error']));
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function addDomainAction(Request $request) : Response
    {
        $localeCode = $request->request->get('localeCode');
        $domain = $request->request->get('domain');
        if (!is_null($localeCode) && !is_null($domain)) {
            $translationPlugin = $this->get('translation_plugin_service');
            $translationPlugin->setLocaleByCode($localeCode);
            $domains = $translationPlugin->getTranslationChecker()->getFullMessageCatalogue()->getDomains();
            if (in_array($domain, $domains)) {
                return new Response(json_encode(['status' => 'error']));
            }
            // TODO: refactor. create empty file?
            $translationPlugin->setDomainMessage($domain, sprintf('%s.description', $domain), '');
            $translationPlugin->writeTranslations();
            return new Response(json_encode(['status' => 'success']));
        }
        return new Response(json_encode(['status' => 'error']));
    }

    // TODO: route to setMessage?
    /**
     * @param Request $request
     * @return Response
     */
    public function addDomainMessageAction(Request $request) : Response
    {
        $localeCode = $request->request->get('localeCode');
        $domain = $request->request->get('domain');
        $messageDomain = $request->request->get('messageDomain');
        $translation = $request->request->get('translation');
        if (!is_null($localeCode) && !is_null($domain) && !is_null($messageDomain) && !is_null($translation)) {
            // TODO: check if translation exists
            $translationPlugin = $this->get('translation_plugin_service');
            $translationPlugin->setLocaleByCode($localeCode);
            $translationPlugin->setDomainMessage($domain, $messageDomain, $translation);
            $translationPlugin->writeTranslations();
            return new Response(json_encode(['status' => 'success']));
        }
        return new Response(json_encode(['status' => 'error']));
    }
}
