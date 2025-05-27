<?php
declare(strict_types=1);

namespace App\Controller\Shop;

use App\Exception\ReorderException;
use App\Service\Order\OrderItemReorderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReorderController extends AbstractController
{
    public function __construct(
        private readonly OrderItemReorderInterface $reorderService,
    )
    {
    }

    #[Route('/shop/reorder/{id}', name: 'shop_reorder_item', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function reorder(Request $request, OrderItemInterface $orderItem): RedirectResponse
    {
        $this->checkCsrfToken($request, $orderItem->getId());

        try {
            $this->reorderService->reorder($orderItem);
            $this->addFlash('success', 'Item added to cart successfully.');
        } catch (ReorderException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('sylius_shop_cart_summary');
    }

    private function checkCsrfToken(Request $request, $orderId): void
    {
        if (!$this->isCsrfTokenValid('reorder_item_' . $orderId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
