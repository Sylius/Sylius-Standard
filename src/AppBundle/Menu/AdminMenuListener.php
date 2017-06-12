<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener {
    /**
     * @param MenuBuilderEvent $event
     */
     public function addAdminMenuItems(MenuBuilderEvent $event)
    {
        $menu = $event->getMenu();

        $newsMenu = $menu
                ->addChild('new')
                ->setLabel('app.ui.uncost')
        ;

        $newsMenu
                ->addChild('new')
                ->setLabel('app.ui.uncost')
                ->setLabelAttribute('icon', 'newspaper')                
        ;
    }
}
