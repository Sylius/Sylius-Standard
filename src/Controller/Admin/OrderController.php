<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController {
    protected OrderRepository $orderRepository;
    protected EntityManagerInterface $manager;

    public function __construct(OrderRepository $orderRepository, EntityManagerInterface $manager) {
        $this->orderRepository = $orderRepository;
        $this->manager         = $manager;
    }

    //this probably should just be patch to  /orders/{id}
    #[Route("/admin/orders/{id}/note", name: "app_admin_order_update_note", methods: ["POST"])]
    public function updateNote(Request $request, int $id): Response {
        $note  = $request->request->get('note');
        $order = $this->orderRepository->find($id);
        $order->setNote($note);
        $this->manager->flush();

        return $this->redirectToRoute('sylius_admin_order_show', ['id' => $order->getId()]);
    }
}
