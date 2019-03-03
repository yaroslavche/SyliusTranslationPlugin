<?php

declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yaroslavche\SyliusTranslationPlugin\Service\TranslationService;

final class TranslationController extends AbstractController
{
    /**
     * @var TranslationService $translationService
     */
    private $translationService;

    /**
     * TranslationController constructor.
     * @param TranslationService $translationService
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * @return Response
     */
    public function dashboardAction(): Response
    {
        $this->translationService->setLocale();

        return $this->render('@YaroslavcheSyliusTranslationPlugin/dashboard.html.twig', [
            'plugin' => $this->translationService
        ]);
    }

    /**
     * @param string|null $localeCode
     * @return Response
     */
    public function localeAction(?string $localeCode): Response
    {
        $this->translationService->setLocaleByCode($localeCode);
        return $this->render('@YaroslavcheSyliusTranslationPlugin/locale.html.twig', [
            'plugin' => $this->translationService
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
            $this->translationService->setLocaleByCode($localeCode);
            $this->translationService->setDomainMessage($domain, $messageDomain, $translation);
            $this->translationService->writeTranslations();
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
