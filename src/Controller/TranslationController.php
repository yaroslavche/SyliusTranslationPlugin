<?php

declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * Frontend
     * how to inject into locales? Like list on edit page and statistics on locales index
     */

    /**
     * @return Response
     */
    public function dashboard(): Response
    {
        return $this->render('@YaroslavcheSyliusTranslationPlugin/dashboard.html.twig', [
            'service' => $this->translationService,
            'pluginTranslationDomain' => TranslationService::PLUGIN_TRANSLATION_DOMAIN,
            'pluginVersion' => '0.2.0'
        ]);
    }

    /**
     * @param string $localeCode
     * @param string|null $domain
     * @return Response
     */
    public function locale(string $localeCode, ?string $domain = null): Response
    {
        $locale = $this->translationService->findLocaleByCode($localeCode);
        if(null === $locale)
        {
            /** @todo add flash message */
            return $this->dashboard();
        }

        return $this->render('@YaroslavcheSyliusTranslationPlugin/locale.html.twig', [
            'service' => $this->translationService,
            'pluginTranslationDomain' => TranslationService::PLUGIN_TRANSLATION_DOMAIN,
            'selectedDomain' => $domain
        ]);
    }



    /**
     * AJAX
     */


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setMessage(Request $request): JsonResponse
    {
        $localeCode = $request->request->get('localeCode', '');
        $locale = $this->translationService->findLocaleByCode($localeCode);
        if (null === $locale) {
            $localeMustBeSetMessage = $this->translationService->findTranslation(
                'locale_must_be_set',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $localeMustBeSetMessage]);
        }

        $domain = $request->request->get('domain');
        if (null === $domain) {
            $domainMustBeSetMessage = $this->translationService->findTranslation(
                'domain_must_be_set',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $domainMustBeSetMessage]);
        }

        $id = $request->request->get('id');
        if (null === $id) {
            $idMustBeSetMessage = $this->translationService->findTranslation(
                'id_must_be_set',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $idMustBeSetMessage]);
        }

        /** @var string|null $translation */
        $translation = $request->request->get('translation');

        try {
            $success = $this->translationService->setMessage($locale, $id, $translation ?? '', $domain);
            return new JsonResponse(['isSet' => $success ? 'true' : 'false']);
        } catch (\Exception $exception) {
            $setMessageErrorMessage = $this->translationService->findTranslation(
                'set_message_error',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $setMessageErrorMessage]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addDomain(Request $request): JsonResponse
    {
        $name = $request->request->get('name', '');
        if (empty($name)) {
            $domainNameMustBeSetMessage = $this->translationService->findTranslation(
                'domain_name_must_be_set',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $domainNameMustBeSetMessage]);
        }

        try {
            $success = $this->translationService->addDomain($name);
            return new JsonResponse(['added' => $success ? 'true' : 'false']);
        } catch (\Exception $exception) {
            $addDomainErrorMessage = $this->translationService->findTranslation(
                'add_domain_message_error',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $addDomainErrorMessage]);
        }
    }
}
