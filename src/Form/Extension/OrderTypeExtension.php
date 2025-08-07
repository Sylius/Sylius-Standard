<?php

declare(strict_types=1);

namespace App\Form\Extension;

use Sylius\Bundle\OrderBundle\Form\Type\OrderType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('note', TextareaType::class, [
            'required' => false,
            'label' => 'sylius.ui.note.label',
            'attr' => [
                'placeholder' => 'sylius.ui.note.placeholder',
            ],
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [OrderType::class];
    }
}
