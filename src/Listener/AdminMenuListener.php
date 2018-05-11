<?php

namespace Acme\SyliusTranslationPlugin\Listener;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $translationSubmenu = $menu
            ->addChild('translation')
            ->setLabel('Translation')
        ;

        $translationSubmenu
            ->addChild('translation_dashboard', ['route' => 'sylius_translation_plugin_translation_locale'])
            ->setLabel('Translation')
        ;
    }
}
