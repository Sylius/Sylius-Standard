<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderConfirmationAdmin
 * Date 26.04.2017
 * @author akorchagin
 */

namespace AppBundle\EmailManager;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;


class OrderConfirmationAdmin {
     /**
     * @var SenderInterface
     */
    private $emailSender;

    /**
     * @var AvailabilityCheckerInterface $availabilityChecker
     */
    private $availabilityChecker;

    /**
     * @var RepositoryInterface $ChannelRepository
     */
    private $ChannelRepository;

    /**
     * @param SenderInterface $emailSender
     * @param AvailabilityCheckerInterface $availabilityChecker
     * @param RepositoryInterface $adminUserRepository
     */
    public function __construct(
        SenderInterface $emailSender,
        AvailabilityCheckerInterface $availabilityChecker,
        RepositoryInterface $adminUserRepository
    ) {
        $this->emailSender = $emailSender;
        $this->availabilityChecker = $availabilityChecker;
        $this->adminUserRepository = $adminUserRepository;
    }

    /**
     * @param OrderInterface $order
     */
    public function sendOrderComfirmAdminkEmailManager()
    {
        // get all admins, but remember to put them into an array
        $channel = $this->channelContext->getChannel();
                $this->emailSender->send('order_confirmation_admin', $channel->getContactEmail());
    }
}
