<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Functional\Controller\Admin;

use Override;
use SyliusOrderNotePlugin\Tests\Support\OrderNoteScenario;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UpdateOrderNoteActionTest extends WebTestCase
{
    private ?OrderNoteScenario $scenario = null;

    #[Override]
    protected function tearDown(): void
    {
        $this->scenario?->rollback();
        $this->scenario = null;
        parent::tearDown();
    }

    public function testAnonymousAccessRedirectsToLogin(): void
    {
        static::createClient()->request('POST', '/admin/orders/1/note');
        self::assertResponseRedirects('/admin/login');
    }

    public function testCreatesAndUpdatesNoteThroughRenderedForm(): void
    {
        $scenario = $this->createScenario();
        $scenario->submit('  Fragile parcel  ');
        self::assertResponseStatusCodeSame(302);
        self::assertSame('Fragile parcel', $scenario->storedContent());
        $scenario->submit('Updated instructions');
        self::assertSame('Updated instructions', $scenario->storedContent());
    }

    public function testDeleteButtonRemovesAnExistingNote(): void
    {
        $scenario = $this->createScenario();
        $scenario->submit('To delete');
        self::assertSame('To delete', $scenario->storedContent());
        $scenario->submit('To delete', delete: true);
        self::assertNull($scenario->storedContent());
    }

    public function testWhitespaceRemovesAnExistingNote(): void
    {
        $scenario = $this->createScenario();
        $scenario->submit('To delete');
        $scenario->submit('   ');
        self::assertNull($scenario->storedContent());
    }

    public function testAccepts500CharactersAndRejects501(): void
    {
        $scenario = $this->createScenario();
        $content = str_repeat('ą', 500);
        $scenario->submit($content);
        self::assertSame($content, $scenario->storedContent());
        $scenario->submit($content . 'a');
        self::assertSame($content, $scenario->storedContent());
    }

    public function testInvalidCsrfCannotChangeOrDeleteNote(): void
    {
        $scenario = $this->createScenario();
        $scenario->submit('Keep this');
        $scenario->submit('Changed', validToken: false);
        self::assertSame('Keep this', $scenario->storedContent());
        $scenario->submit('', delete: true, validToken: false);
        self::assertSame('Keep this', $scenario->storedContent());
    }

    public function testNoteIsEscapedInAdminForm(): void
    {
        $scenario = $this->createScenario();
        $content = '<script>alert(1)</script>';
        $scenario->submit($content);
        $crawler = $scenario->open();
        self::assertSame($content, $crawler->filter('textarea[name="sylius_order_note[note]"]')->text());
        self::assertSame(0, $crawler->filter('[data-test-order-note-section] script')->count());
    }

    private function createScenario(): OrderNoteScenario
    {
        $client = static::createClient();

        return $this->scenario = new OrderNoteScenario($client, static::getContainer());
    }
}
