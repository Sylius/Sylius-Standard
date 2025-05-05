<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Repository\OrderItemRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReorderItemController extends AbstractController
{
    #[Route('/reorder/item/{id}', name: 'app_reorder_item', methods: ['POST'])]
    public function __invoke(
        int                          $id,
        OrderItemRepositoryInterface $orderItemRepository,
        CartContextInterface         $cartContext,
        OrderModifierInterface       $orderModifier,
        EntityManagerInterface       $cartManager,
    ): RedirectResponse
    {
        /** @var OrderItemInterface|null $orderItem */
        $orderItem = $orderItemRepository->find($id);

        if (!$orderItem) {
            $this->addFlash('error', 'Order item not found.');
            return $this->redirectToRoute('sylius_shop_account_order_index');
        }

        $redirectResponse = $this->redirectToRoute('sylius_shop_account_order_show', ['number' => $orderItem->getOrder()->getNumber()]);

        $variant = $orderItem->getVariant();
        if (!$variant || !$variant->isEnabled() || $variant->getOnHand() <= 0) {
            $this->addFlash('error', 'This product is not available for reorder.');
            return $redirectResponse;
        }

        $cart = $cartContext->getCart();
        $newItem = clone $orderItem;

        $newItem->setOrder($cart);

        $cartManager->persist($cart);
        $cartManager->flush();

        $this->addFlash('success', 'Product added to your cart!');
        return $redirectResponse;
    }
}
