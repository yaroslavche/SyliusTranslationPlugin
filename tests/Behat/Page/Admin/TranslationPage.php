<?php

declare(strict_types=1);

namespace Tests\Yaroslavche\SyliusTranslationPlugin\Behat\Page\Admin;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class TranslationPage extends SymfonyPage implements TranslationPageInterface
{
    /**
     * {@inheritdoc}
     * @todo remove dummy and implement logic
     */
    public function getTranslation(): string
    {
        return $this->getSession()->getPage()->waitFor(3, function (): string {
            $translation = $this->getElement('translation')->getText();

            return $translation;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteName(): string
    {
        return 'yaroslavche_sylius_translation_plugin_page';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'translation' => '#translation',
        ]);
    }
}
