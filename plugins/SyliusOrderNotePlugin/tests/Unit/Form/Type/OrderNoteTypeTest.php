<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Unit\Form\Type;

use SyliusOrderNotePlugin\Entity\OrderNote;
use SyliusOrderNotePlugin\Form\Type\OrderNoteType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class OrderNoteTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $orderNote = new OrderNote();
        $form = $this->factory->create(OrderNoteType::class, $orderNote);

        $formData = [
            'note' => 'To jest ważna notatka dotycząca zamówienia #0001',
        ];

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('To jest ważna notatka dotycząca zamówienia #0001', $orderNote->getNote());
    }

    public function testSubmitEmptyData(): void
    {
        $orderNote = new OrderNote();
        $orderNote->setNote('Poprzednia notatka');

        $form = $this->factory->create(OrderNoteType::class, $orderNote);

        $formData = [
            'note' => '',
        ];

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertNull($orderNote->getNote());
    }
}
