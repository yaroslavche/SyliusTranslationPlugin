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
        return $this->render('@YaroslavcheSyliusTranslationPlugin/base.html.twig');
    }

    /** AJAX */

    /**
     * @return JsonResponse
     */
    public function getLocales(): JsonResponse
    {
        return $this->json(['status' => 'success', 'locales' => $this->translationService->getLocales()]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessageCatalogue(Request $request): JsonResponse
    {
//        sleep(mt_rand(0, 3));
        $requestLocaleCode = $request->request->get('localeCode');
        $messageCatalogue = $this->translationService->getMessageCatalogue($requestLocaleCode);

        if (null === $messageCatalogue) {
            return $this->json(['status' => 'error', 'message' => 'get_message_catalogue_failed']);
        }
        return $this->json(['status' => 'success', 'messageCatalogue' => $messageCatalogue->all()]);
    }
}
