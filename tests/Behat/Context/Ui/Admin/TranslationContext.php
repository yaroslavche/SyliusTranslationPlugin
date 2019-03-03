<?php
declare(strict_types=1);

namespace Tests\Yaroslavche\SyliusTranslationPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
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
     * @When I open translation admin page
     */
    public function iOpenTranslationPage(): void
    {
        $this->translationPage->open();
    }
}
