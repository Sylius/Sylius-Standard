<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Controller\Admin;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use SyliusOrderNotePlugin\Form\Factory\OrderNoteFormFactoryInterface;
use SyliusOrderNotePlugin\Form\Model\OrderNoteData;
use SyliusOrderNotePlugin\Updater\OrderNoteUpdaterInterface;
use Symfony\Component\Form\ClickableInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Webmozart\Assert\Assert;

final class UpdateOrderNoteAction
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderNoteFormFactoryInterface $formFactory,
        private readonly OrderNoteUpdaterInterface $updater,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(
        Request $request,
        int|string $id,
    ): Response {
        if (false === $this->authorizationChecker->isGranted('ROLE_ADMINISTRATION_ACCESS')) {
            throw new AccessDeniedException();
        }

        $order = $this->orderRepository->find($id);
        if (!$order instanceof OrderInterface || !$order instanceof OrderNoteAwareInterface) {
            throw new NotFoundHttpException('Order supporting internal notes was not found.');
        }

        $form = $this->formFactory->create($order);
        $form->handleRequest($request);
        $session = $request->getSession();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            Assert::isInstanceOf($data, OrderNoteData::class);
            $delete = $form->get('delete');
            $content = $delete instanceof ClickableInterface && $delete->isClicked() ? null : $data->note;
            $this->updater->update($order, $content);

            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add(
                    'success',
                    null === $order->getOrderNote()
                    ? 'sylius_order_note.order.note.deleted_successfully'
                    : 'sylius_order_note.order.note.updated_successfully',
                );
            }
        } elseif ($session instanceof FlashBagAwareSessionInterface) {
            foreach ($form->getErrors(true) as $error) {
                if ($error instanceof FormError) {
                    $session->getFlashBag()->add('error', $error->getMessage());
                }
            }
        }

        return new RedirectResponse($this->urlGenerator->generate('sylius_admin_order_show', ['id' => $id]));
    }
}
