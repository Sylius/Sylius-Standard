<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

/**
 * Description of OrderRestoreController
 *
 * @author akorc_000
 */

use Sylius\Bundle\CoreBundle\Controller\OrderController as ResourceController;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
     * @param Request $request
     *
     * @return RedirectResponse
     */
class OrderRestoreController extends ResourceController{
    public function changeAllOrderStatus(Request $request) {
       
        var_dump($request->getContent());
        return $this->applyStateMachineTransitionAction($request);
    }
}
