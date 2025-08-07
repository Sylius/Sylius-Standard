<?php

declare(strict_types=1);

namespace App\Tests\Entity\Order;

use App\Entity\Order\Order;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validation;

class OrderTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        $this->order = new Order();
    }

    public function test_default_note_is_null(): void
    {
        self::assertNull($this->order->getNote());
    }

    public function test_set_and_get_note(): void
    {
        $this->order->setNote('Customer prefers evening delivery');

        $this->assertSame('Customer prefers evening delivery', $this->order->getNote());
    }

    public function test_note_length_validation(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $longNote = str_repeat('a', 501);
        $this->order->setNote($longNote);

        $violations = $validator->validateProperty($this->order, 'note', ['sylius_shipping_address_update']);

        $this->assertCount(1, $violations);

        /** @var ConstraintViolationInterface $violation */
        $violation = $violations[0];
        $this->assertSame('sylius.ui.note.valid.too_long', $violation->getMessage());
    }
}
