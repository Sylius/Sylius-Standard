<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Form\Type;

use SyliusOrderNotePlugin\Entity\OrderNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

final class OrderNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', TextareaType::class, [
                'label' => 'app.ui.note',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 500,
                    'placeholder' => 'app.ui.order_note_placeholder',
                ],
                'constraints' => [
                    new Length([
                        'max' => 500,
                        'maxMessage' => 'app.order.note.max_length',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderNote::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'order_note',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'app_order_note';
    }
}
