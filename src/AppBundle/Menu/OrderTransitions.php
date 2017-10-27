<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Menu;

/**
 * Description of OrderTransitions
 *
 * @author akorc_000
 */


final class OrderTransitions{

    const TRANSITION_RESTORE = 'restore';
    const TRANSITION_RECANCEL = 'cancel_from_reopen';
    
    
    private function __construct()
    {
    }
    
}
