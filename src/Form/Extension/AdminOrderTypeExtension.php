<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Order\Order;
use Sylius\Bundle\AdminBundle\Form\Type\OrderType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;

class AdminOrderTypeExtension extends AbstractTypeExtension
{
    private const TEXTAREA_ROWS = 3;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('adminNotes', TextareaType::class, $this->getAdminNotesFieldOptions());
    }

    /**
     * @return array<string, mixed>
     */
    private function getAdminNotesFieldOptions(): array
    {
        return [
            'label' => 'sylius.order.admin_notes.label',
            'required' => false,
            'constraints' => [
                new Length([
                    'max' => Order::MAX_NOTE_LENGTH,
                    'maxMessage' => 'sylius.order.admin_notes.max_length',
                ]),
            ],
            'attr' => [
                'rows' => self::TEXTAREA_ROWS,
                'maxlength' => Order::MAX_NOTE_LENGTH,
                'placeholder' => 'sylius.order.admin_notes.placeholder',
                'data-char-limit' => Order::MAX_NOTE_LENGTH,
            ],
        ];
    }

    public static function getExtendedTypes(): iterable
    {
        return [OrderType::class];
    }
}
