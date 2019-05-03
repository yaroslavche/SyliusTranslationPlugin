<?php

declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Yaroslavche\SyliusTranslationPlugin\Service\TranslationService;

use function Safe\sprintf;

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
        $supportedLocales = Intl::getLocaleBundle()->getLocaleNames($this->translationService->getDefaultLocale());
        return $this->json([
            'status' => 'success',
            'locales' => $this->translationService->getLocales(),
            'supportedLocales' => $supportedLocales,
            /** @todo */
            'defaultLocale' => 'en_US'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getMessageCatalogue(Request $request): JsonResponse
    {
        $requestLocaleCode = $request->request->get('localeCode');
        $messageCatalogue = $this->translationService->getMessageCatalogue($requestLocaleCode);
        $customMessageCatalogue = $this->translationService->getCustomMessageCatalogue($requestLocaleCode);

        if (null === $messageCatalogue) {
            return $this->json(['status' => 'error', 'message' => 'get_message_catalogue_failed']);
        }
        return $this->json([
            'status' => 'success',
            'messageCatalogue' => $messageCatalogue->all(),
            'customMessageCatalogue' => $customMessageCatalogue ? $customMessageCatalogue->all() : null
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function setMessage(Request $request): JsonResponse
    {
        $localeCode = $request->request->get('localeCode');
        $domain = $request->request->get('domain');
        $id = $request->request->get('id');
        $message = $request->request->get('message', '');

        try {
            $this->translationService->setMessage($localeCode, $domain, $id, $message ?? '');
        } catch (Exception $exception) {
            return $this->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }

        return $this->json([
            'status' => 'success',
            'message' => 'Message successfully updated.'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addLocale(Request $request): JsonResponse
    {
        $requestLocaleCode = $request->request->get('localeCode');
        try {
            $locale = $this->translationService->addLocale($requestLocaleCode);
            $message = sprintf('Successfully added locale "%s" (%s)', $locale->getName(), $locale->getCode());
            return $this->json(['status' => 'success', 'message' => $message, 'localeLanguageName' => $locale->getName()]);
        } catch (Exception $exception) {
            return $this->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeLocale(Request $request): JsonResponse
    {
        $requestLocaleCode = $request->request->get('localeCode');
        try {
            $this->translationService->removeLocale($requestLocaleCode);
            $message = sprintf('Successfully removed locale code "%s"', $requestLocaleCode);
            return $this->json(['status' => 'success', 'message' => $message]);
        } catch (Exception $exception) {
            return $this->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }
}
