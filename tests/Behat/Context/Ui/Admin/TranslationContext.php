<?php
declare(strict_types=1);

namespace Tests\Yaroslavche\SyliusTranslationPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Tests\Yaroslavche\SyliusTranslationPlugin\Behat\Page\Admin\TranslationPageInterface;
use Webmozart\Assert\Assert;

final class TranslationContext implements Context
{
    /**
     * @var TranslationPageInterface
     */
    private $translationPage;

    /**
     * @param TranslationPageInterface $translationPage
     */
    public function __construct(TranslationPageInterface $translationPage)
    {
        $this->translationPage = $translationPage;
    }

    /**
     * @Given I send request to set locale :locale translation :translation for id :id in domain :domain
     */
    public function iSendRequestToSetLocaleTranslationForIdInDomain($locale, $translation, $id, $domain)
    {
        throw new PendingException();
    }

    /**
     * @Then locale :locale translation for id :id in domain :domain should be :translation
     */
    public function localeTranslationForIdInDomainShouldBe($locale, $id, $domain, $translation)
    {
        throw new PendingException();
    }

    /**
     * @Then last response should be error with text :error
     */
    public function lastResponseShouldBeErrorWithText($error)
    {
        throw new PendingException();
    }



}
