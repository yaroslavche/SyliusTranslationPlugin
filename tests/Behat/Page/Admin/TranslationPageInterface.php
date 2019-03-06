<?php

declare(strict_types=1);

namespace Tests\Yaroslavche\SyliusTranslationPlugin\Behat\Page\Admin;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface TranslationPageInterface extends SymfonyPageInterface
{
    /**
     * @return string
     */
    public function getTranslation(): string;
}
