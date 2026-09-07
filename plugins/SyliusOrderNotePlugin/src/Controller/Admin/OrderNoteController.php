<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use SyliusOrderNotePlugin\Entity\OrderNote;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use SyliusOrderNotePlugin\Form\Type\OrderNoteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMINISTRATION_ACCESS')]
final class OrderNoteController extends AbstractController
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/admin/orders/{id}/note', name: 'app_admin_order_note_update', methods: ['POST'])]
    public function update(Request $request, int|string $id): Response
    {
        /** @var (OrderInterface&OrderNoteAwareInterface)|null $order */
        $order = $this->orderRepository->find($id);

        if (null === $order) {
            throw new NotFoundHttpException(sprintf('Order with ID %s not found.', (string) $id));
        }

        $orderNote = $order->getOrderNote() ?? new OrderNote();
        if (null === $orderNote->getOrder()) {
            $orderNote->setOrder($order);
        }

        $form = $this->createForm(OrderNoteType::class, $orderNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $noteContent = $orderNote->getNote();

            if (null === $noteContent || trim($noteContent) === '') {
                if (null !== $orderNote->getId()) {
                    $order->setOrderNote(null);
                    $this->entityManager->remove($orderNote);
                }
            } else {
                $orderNote->setNote(trim($noteContent));
                $orderNote->setUpdatedAt(new \DateTimeImmutable());
                $order->setOrderNote($orderNote);
                $this->entityManager->persist($orderNote);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'app.order.note.updated_successfully');
        } else {
            /** @var \Symfony\Component\Form\FormError $error */
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->redirectToRoute('sylius_admin_order_show', ['id' => $id]);
    }
}
