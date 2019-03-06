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
    public function locale(string $localeCode, string $domain = 'messages'): Response
    {
        $locale = $this->translationService->findLocaleByCode($localeCode);
        if (null === $locale) {
            /** @todo add flash message Locale must be set */
            return $this->dashboard();
        }

        $this->translationService->setCurrentLocale($locale);
        $syliusLocaleMessageCatalogue = $this->translationService->getSyliusLocaleMessageCatalogue($locale);
        $domains = $syliusLocaleMessageCatalogue->getFullMessageCatalogue()->getDomains();
        if (!in_array($domain, $domains)) {
            /** @todo add flash message Domain $domain not found */
            return $this->dashboard();
        }

        return $this->render('@YaroslavcheSyliusTranslationPlugin/locale.html.twig', [
            'service' => $this->translationService,
            'pluginTranslationDomain' => TranslationService::PLUGIN_TRANSLATION_DOMAIN
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
            if ($success) {
                $message = $this->translationService->findTranslation(
                    'set_message_success',
                    TranslationService::PLUGIN_TRANSLATION_DOMAIN
                );
            } else {
                $message = $this->translationService->findTranslation(
                    'set_message_failed',
                    TranslationService::PLUGIN_TRANSLATION_DOMAIN
                );
            }
            return new JsonResponse(['status' => $success ? 'success' : 'error', 'message' => $message]);
        } catch (\Exception $exception) {
            $setMessageErrorMessage = $this->translationService->findTranslation(
                'set_message_error',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $setMessageErrorMessage]);
        }
    }

    /**
     * @param string|null $localeCode
     * @return JsonResponse
     */
    public function getTranslationMessageCatalogue(?string $localeCode = null): JsonResponse
    {
        $locale = null;
        if (null === $localeCode) {
            $locale = $this->translationService->getDefaultLocale();
        } else {
            $locale = $this->translationService->findLocaleByCode($localeCode);
        }
        if (null === $locale) {
            $localeMustBeSetMessage = $this->translationService->findTranslation(
                'locale_must_be_set',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $localeMustBeSetMessage]);
        }

        try {
            $localeMessageCatalogue = array_merge(
                $this->translationService->getFullMessageCatalogue()->all(),
                $this->translationService->getSyliusLocaleMessageCatalogue($locale)->getMessageCatalogue()->all(),
                $this->translationService->getSyliusLocaleMessageCatalogue($locale)->getCustomMessageCatalogue()->all()
            );
            return new JsonResponse(['status' => 'success', 'messageCatalogue' => $localeMessageCatalogue]);
        } catch (\Exception $exception) {
            $addDomainErrorMessage = $this->translationService->findTranslation(
                'add_domain_message_error',
                TranslationService::PLUGIN_TRANSLATION_DOMAIN
            );
            return new JsonResponse(['status' => 'error', 'message' => $addDomainErrorMessage]);
        }
    }
}
