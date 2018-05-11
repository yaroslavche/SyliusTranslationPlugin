<?php

declare(strict_types=1);

namespace Acme\SyliusTranslationPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TranslationController extends Controller
{
    /**
     * @param string|null $localeCode
     * @return Response
     */
    public function translationAction(?string $localeCode): Response
    {
        $translationPlugin = $this->get('translation_plugin_service');
        $translationPlugin->setLocaleByCode($localeCode);
        // $translationPlugin->checkTranslations();

        return $this->render('@AcmeSyliusTranslationPlugin/translation.html.twig', [
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
}
