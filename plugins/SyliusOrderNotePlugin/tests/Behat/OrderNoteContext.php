<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Behat;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use SyliusOrderNotePlugin\Tests\Support\OrderNoteScenario;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Webmozart\Assert\Assert as TypeAssert;

final class OrderNoteContext implements Context
{
    private ?OrderNoteScenario $scenario = null;

    private ?KernelInterface $kernel = null;

    /**
     * @Given I am an administrator viewing an order
     */
    public function prepareOrder(): void
    {
        $kernelClass = $_SERVER['KERNEL_CLASS'] ?? $_ENV['KERNEL_CLASS'] ?? '';
        TypeAssert::subclassOf($kernelClass, KernelInterface::class);
        $this->kernel = new $kernelClass('test', false);
        $this->kernel->boot();
        $container = $this->kernel->getContainer()->get('test.service_container');
        Assert::assertInstanceOf(ContainerInterface::class, $container);
        $this->scenario = new OrderNoteScenario(new KernelBrowser($this->kernel), $container);
    }

    /**
     * @When I save the note :content
     */
    public function saveNote(string $content): void
    {
        $this->scenario()->submit($content);
    }

    /**
     * @When I delete the note
     */
    public function deleteNote(): void
    {
        $this->scenario()->submit('', delete: true);
    }

    /**
     * @When I submit a note with 501 characters
     */
    public function submitLongNote(): void
    {
        $this->scenario()->submit(str_repeat('a', 501));
    }

    /**
     * @When I submit a note with an invalid CSRF token
     */
    public function submitInvalidToken(): void
    {
        $this->scenario()->submit('Changed', validToken: false);
    }

    /**
     * @Then the stored note should be :content
     */
    public function assertNote(string $content): void
    {
        Assert::assertSame($content, $this->scenario()->storedContent());
    }

    /**
     * @Then the order should have no note
     */
    public function assertNoNote(): void
    {
        Assert::assertNull($this->scenario()->storedContent());
    }

    /**
     * @AfterScenario
     */
    public function cleanUp(): void
    {
        $this->scenario?->rollback();
        $this->kernel?->shutdown();
        $this->scenario = null;
        $this->kernel = null;
    }

    private function scenario(): OrderNoteScenario
    {
        Assert::assertNotNull($this->scenario);

        return $this->scenario;
    }
}
