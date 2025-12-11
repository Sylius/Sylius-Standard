<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context\Admin\Order;

use Behat\Behat\Context\Context;
use Sylius\Behat\Page\Admin\Order\ShowPageInterface;
use Sylius\Behat\Page\Admin\Order\UpdatePageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Webmozart\Assert\Assert;

final class AdminNotesContext implements Context
{
    public function __construct(
        private readonly ShowPageInterface $showPage,
        private readonly UpdatePageInterface $updatePage,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    /**
     * @When I am on the order :orderNumber details page
     */
    public function iAmOnTheOrderDetailsPage(string $orderNumber): void
    {
        $order = $this->findOrderByNumber($orderNumber);
        $this->showPage->open(['id' => $order->getId()]);
    }

    /**
     * @Then I should be on the order :orderNumber details page
     */
    public function iShouldBeOnTheOrderDetailsPage(string $orderNumber): void
    {
        $order = $this->findOrderByNumber($orderNumber);
        Assert::true(
            $this->showPage->isOpen(['id' => $order->getId()]),
            sprintf('Expected to be on order "%s" details page, but was not', $orderNumber),
        );
    }

    /**
     * @Then I should see the admin notes section
     */
    public function iShouldSeeTheAdminNotesSection(): void
    {
        $session = $this->showPage->getSession();
        $page = $session->getPage();
        Assert::true($page->has('css', '[data-test-admin-notes]'));
    }

    /**
     * @Then the admin notes section should be empty
     */
    public function theAdminNotesSectionShouldBeEmpty(): void
    {
        $session = $this->showPage->getSession();
        $page = $session->getPage();
        $adminNotesElement = $page->find('css', '[data-test-admin-notes]');
        Assert::notNull($adminNotesElement);
        $text = $adminNotesElement->getText();
        Assert::contains($text, '-');
    }

    /**
     * @When I click :buttonText button in admin notes section
     */
    public function iClickButtonInAdminNotesSection(string $buttonText): void
    {
        $session = $this->showPage->getSession();
        $page = $session->getPage();
        $link = $page->findLink($buttonText);
        Assert::notNull($link);
        $link->click();
    }

    /**
     * @When I fill in :field with :value
     */
    public function iFillInWith(string $field, string $value): void
    {
        $session = $this->updatePage->getSession();
        $page = $session->getPage();
        $fieldElement = $page->findField($field);
        Assert::notNull($fieldElement);
        $fieldElement->setValue($value);
    }

    /**
     * @When I save the form
     */
    public function iSaveTheForm(): void
    {
        $this->updatePage->saveChanges();
    }

    /**
     * @Then I should see :text in admin notes section
     */
    public function iShouldSeeInAdminNotesSection(string $text): void
    {
        $session = $this->showPage->getSession();
        $page = $session->getPage();
        $adminNotesElement = $page->find('css', '[data-test-admin-notes]');
        Assert::notNull($adminNotesElement);
        Assert::contains($adminNotesElement->getText(), $text);
    }

    /**
     * @When I clear the :field field
     */
    public function iClearTheField(string $field): void
    {
        $session = $this->updatePage->getSession();
        $page = $session->getPage();
        $fieldElement = $page->findField($field);
        Assert::notNull($fieldElement);
        $fieldElement->setValue('');
    }

    /**
     * @When I fill in :field with a string of :length characters
     */
    public function iFillInWithAStringOfCharacters(string $field, int $length): void
    {
        $longString = str_repeat('a', $length);
        $session = $this->updatePage->getSession();
        $page = $session->getPage();
        $fieldElement = $page->findField($field);
        Assert::notNull($fieldElement);
        $fieldElement->setValue($longString);
    }

    /**
     * @Then I should see validation error :message
     */
    public function iShouldSeeValidationError(string $message): void
    {
        $session = $this->updatePage->getSession();
        $page = $session->getPage();
        $errorText = $page->getText();
        Assert::contains($errorText, $message);
    }

    /**
     * @When I set admin notes :note to order :orderNumber
     */
    public function iSetAdminNotesToOrder(string $note, string $orderNumber): void
    {
        $order = $this->findOrderByNumber($orderNumber);
        $order->setAdminNotes($note);
        $this->orderRepository->add($order);
    }

    /**
     * @Then the admin notes should be truncated to :length characters
     */
    public function theAdminNotesShouldBeTruncatedToCharacters(int $length): void
    {
        $session = $this->showPage->getSession();
        $page = $session->getPage();
        $adminNotesElement = $page->find('css', '[data-test-admin-notes]');
        Assert::notNull($adminNotesElement);
        $text = $adminNotesElement->getText();
        $noteText = preg_replace('/Notes \(Admin\):.*?Edit/', '', $text);
        $noteText = trim($noteText);
        Assert::length($noteText, $length);
    }

    private function findOrderByNumber(string $orderNumber): OrderInterface
    {
        $orderNumber = ltrim($orderNumber, '#');
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        Assert::notNull($order, sprintf('Order with number "%s" not found', $orderNumber));

        return $order;
    }
}
