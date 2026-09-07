<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Functional\Controller\Admin;

use App\Entity\Order\Order;
use App\Entity\User\AdminUser;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderNoteControllerTest extends WebTestCase
{
    public function testUnauthorizedAccessRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('POST', '/admin/orders/1/note', [
            'app_order_note' => [
                'note' => 'Unauthorized test note',
            ],
        ]);

        $this->assertResponseRedirects('/admin/login');
    }

    public function testAdminCanAddAndSaveOrderNote(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var UserRepositoryInterface<AdminUserInterface> $adminUserRepository */
        $adminUserRepository = $container->get('sylius.repository.admin_user');
        /** @var AdminUser|null $adminUser */
        $adminUser = $adminUserRepository->findOneBy(['email' => 'sylius@example.com']);

        if (null === $adminUser) {
            $this->markTestSkipped('Admin user sylius@example.com not found.');
        }

        $client->loginUser($adminUser, 'admin');

        /** @var OrderRepositoryInterface<Order> $orderRepository */
        $orderRepository = $container->get('sylius.repository.order');
        /** @var Order|null $order */
        $order = $orderRepository->findOneBy([]);

        if (null === $order) {
            $this->markTestSkipped('No order available in test database.');
        }

        $crawler = $client->request('GET', sprintf('/admin/orders/%d', $order->getId()));
        $this->assertResponseIsSuccessful();

        $token = $crawler->filter('input[name="app_order_note[_token]"]')->attr('value');

        $client->request('POST', sprintf('/admin/orders/%d/note', $order->getId()), [
            'app_order_note' => [
                'note' => 'Nowa notatka w osobnej tabeli dla zamówienia',
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects(sprintf('/admin/orders/%d', $order->getId()));

        $client->followRedirect();
        $this->assertResponseIsSuccessful();

        $updatedOrder = $orderRepository->find($order->getId());
        $this->assertNotNull($updatedOrder);
        $this->assertNotNull($updatedOrder->getOrderNote());
        $this->assertSame('Nowa notatka w osobnej tabeli dla zamówienia', $updatedOrder->getOrderNote()->getNote());
    }

    public function testAdminCanDeleteOrderNote(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var UserRepositoryInterface<AdminUserInterface> $adminUserRepository */
        $adminUserRepository = $container->get('sylius.repository.admin_user');
        /** @var AdminUser|null $adminUser */
        $adminUser = $adminUserRepository->findOneBy(['email' => 'sylius@example.com']);

        if (null === $adminUser) {
            $this->markTestSkipped('Admin user sylius@example.com not found.');
        }

        $client->loginUser($adminUser, 'admin');

        /** @var OrderRepositoryInterface<Order> $orderRepository */
        $orderRepository = $container->get('sylius.repository.order');
        /** @var Order|null $order */
        $order = $orderRepository->findOneBy([]);

        if (null === $order) {
            $this->markTestSkipped('No order available in test database.');
        }

        $crawler = $client->request('GET', sprintf('/admin/orders/%d', $order->getId()));
        $token = $crawler->filter('input[name="app_order_note[_token]"]')->attr('value');

        $client->request('POST', sprintf('/admin/orders/%d/note', $order->getId()), [
            'app_order_note' => [
                'note' => '',
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects(sprintf('/admin/orders/%d', $order->getId()));

        $updatedOrder = $orderRepository->find($order->getId());
        $this->assertNotNull($updatedOrder);
        $this->assertNull($updatedOrder->getOrderNote());
    }

    public function testSubmittingNoteExceeding500CharactersFails(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var UserRepositoryInterface<AdminUserInterface> $adminUserRepository */
        $adminUserRepository = $container->get('sylius.repository.admin_user');
        /** @var AdminUser|null $adminUser */
        $adminUser = $adminUserRepository->findOneBy(['email' => 'sylius@example.com']);

        if (null === $adminUser) {
            $this->markTestSkipped('Admin user sylius@example.com not found.');
        }

        $client->loginUser($adminUser, 'admin');

        /** @var OrderRepositoryInterface<Order> $orderRepository */
        $orderRepository = $container->get('sylius.repository.order');
        /** @var Order|null $order */
        $order = $orderRepository->findOneBy([]);

        if (null === $order) {
            $this->markTestSkipped('No order available in test database.');
        }

        $crawler = $client->request('GET', sprintf('/admin/orders/%d', $order->getId()));
        $token = $crawler->filter('input[name="app_order_note[_token]"]')->attr('value');
        $longNote = str_repeat('a', 501);

        $client->request('POST', sprintf('/admin/orders/%d/note', $order->getId()), [
            'app_order_note' => [
                'note' => $longNote,
                '_token' => $token,
            ],
        ]);

        $this->assertResponseRedirects(sprintf('/admin/orders/%d', $order->getId()));

        $updatedOrder = $orderRepository->find($order->getId());
        $this->assertNotNull($updatedOrder);
        if (null !== $updatedOrder->getOrderNote()) {
            $this->assertNotSame($longNote, $updatedOrder->getOrderNote()->getNote());
        }
    }
}
