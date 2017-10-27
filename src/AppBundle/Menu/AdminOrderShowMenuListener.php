<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Menu;

/**
 * Description of AdminOrderShowMenuListener
 *
 * @author akorc_000
 */
use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use AppBundle\Menu\OrderTransitions;


final class AdminOrderShowMenuListener
{
    /**
     * @param OrderShowMenuBuilderEvent $event
     */
    public function addAdminOrderShowMenuItems(OrderShowMenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();
        $order = $event->getOrder();
        $stateMachine = $event->getStateMachine();
        
        if ($stateMachine->can(OrderTransitions::TRANSITION_RESTORE)) {
            $menu
                ->addChild('restore', [
                    'route' => 'sylius_admin_order_restore',
                    'routeParameters' => ['id' => $order->getId()]
                ])
                ->setAttribute('type', 'transition')
                ->setLabel('sylius.ui.restore')
                ->setLabelAttribute('icon', 'checkmark')
                ->setLabelAttribute('color', 'green')
            ;
        }
        
        if ($stateMachine->can(OrderTransitions::TRANSITION_RECANCEL)) {
            $menu
                ->addChild('cancel', [
                    'route' => 'sylius_admin_order_recancel',
                    'routeParameters' => ['id' => $order->getId()]
                ])
                ->setAttribute('type', 'transition')
                ->setLabel('sylius.ui.cancel')
                ->setLabelAttribute('icon', 'ban')
                ->setLabelAttribute('color', 'yellow')
            ;
        }        
    }
}
