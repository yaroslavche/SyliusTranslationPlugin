<?php

declare(strict_types=1);

namespace Yaroslavche\SyliusTranslationPlugin\Controller;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function fetchLocalesData(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'defaultLocaleCode' => $this->translationService->getDefaultLocaleCode(),
            'availableLocales' => $this->translationService->getAvailableLocales(),
            'supportedLocales' => $this->translationService->getSupportedLocales()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchFullMessageCatalogue(Request $request): JsonResponse
    {
        try {
            $full = $this->translationService->getFullMessageCatalogue();
            return $this->json([
                'status' => 'success',
                'full' => $full->all()
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchLocaleMessageCatalogues(Request $request): JsonResponse
    {
        $localeCode = $request->request->get('localeCode');
        $localeCode = $localeCode === 'en_US' ? 'en' : $localeCode;
        try {
            $translated = $this->translationService->getTranslatedMessageCatalogue($localeCode);
            $custom = $this->translationService->getCustomMessageCatalogue($localeCode);
        } catch (Exception $exception) {
            return $this->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }

        return $this->json([
            'status' => 'success',
            'translated' => $translated->all(),
            'custom' => $custom->all()
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
