<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Support;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\Alice\Loader\NativeLoader;
use PHPUnit\Framework\Assert as TestAssert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

final class OrderNoteScenario
{
    private Connection $connection;

    private int $orderId;

    public function __construct(
        public readonly KernelBrowser $client,
        ContainerInterface $container,
    ) {
        $client->disableReboot();
        $client->catchExceptions(false);
        $manager = $container->get('doctrine.orm.entity_manager');
        Assert::isInstanceOf($manager, EntityManagerInterface::class);
        $this->connection = $manager->getConnection();
        $this->connection->beginTransaction();

        try {
            $yaml = file_get_contents(__DIR__ . '/../Fixtures/order.yaml');
            Assert::string($yaml);
            foreach (['admin_user', 'channel', 'customer', 'order'] as $resource) {
                $model = $container->getParameter('sylius.model.' . $resource . '.class');
                Assert::string($model);
                $yaml = str_replace('%sylius.model.' . $resource . '.class%', $model, $yaml);
            }

            $objects = [];
            foreach (['currency' => 'USD', 'locale' => 'en_US'] as $resource => $code) {
                $class = $container->getParameter('sylius.model.' . $resource . '.class');
                Assert::classExists($class);
                $object = $manager->getRepository($class)->findOneBy(['code' => $code]);
                if (null === $object) {
                    $loaded = (new NativeLoader())->loadData([$class => ['reference' => ['code' => $code]]]);
                    $object = $loaded->getObjects()['reference'];
                    $manager->persist($object);
                }
                $objects['note_' . $resource] = $object;
            }

            $data = Yaml::parse($yaml);
            Assert::isArray($data);
            $fixtures = (new NativeLoader())->loadData($data, ['suffix' => bin2hex(random_bytes(6))], $objects)->getObjects();
            foreach ($fixtures as $object) {
                $manager->persist($object);
            }
            $manager->flush();

            $order = $fixtures['note_order'];
            $identifier = $manager->getClassMetadata($order::class)->getIdentifierValues($order)['id'];
            Assert::integer($identifier);
            $this->orderId = $identifier;
            $admin = $fixtures['note_admin'];
            Assert::isInstanceOf($admin, UserInterface::class);
            $client->loginUser($admin, 'admin');
        } catch (\Throwable $exception) {
            $this->rollback();

            throw $exception;
        }
    }

    public function open(): Crawler
    {
        $crawler = $this->client->request('GET', '/admin/orders/' . $this->orderId);
        TestAssert::assertSame(200, $this->client->getResponse()->getStatusCode(), (string) $this->client->getResponse()->headers->get('Location'));

        return $crawler;
    }

    public function submit(
        string $content,
        bool $delete = false,
        bool $validToken = true,
    ): void {
        $crawler = $this->open();
        $token = $crawler->filter('input[name="sylius_order_note[_token]"]')->attr('value');
        $this->client->request(
            'POST',
            '/admin/orders/' . $this->orderId . '/note',
            [
            'sylius_order_note' => [
                'note' => $content,
                '_token' => $validToken ? $token : 'invalid-token',
                $delete ? 'delete' : 'save' => '',
            ],
            ],
        );
    }

    public function storedContent(): ?string
    {
        $value = $this->connection->fetchOne('SELECT note FROM sylius_order_note WHERE order_id = ?', [$this->orderId]);
        Assert::nullOrString(false === $value ? null : $value);

        return false === $value ? null : $value;
    }

    public function rollback(): void
    {
        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
    }
}
