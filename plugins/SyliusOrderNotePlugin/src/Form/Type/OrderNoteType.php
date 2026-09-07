<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Form\Type;

use Override;
use SyliusOrderNotePlugin\Entity\OrderNoteInterface;
use SyliusOrderNotePlugin\Form\Model\OrderNoteData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class OrderNoteType extends AbstractType
{
    #[Override]
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add(
                'note',
                TextareaType::class,
                [
                'label' => 'sylius_order_note.ui.note_content',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => OrderNoteInterface::MAX_LENGTH,
                    'placeholder' => 'sylius_order_note.ui.order_note_placeholder',
                ],
                ],
            )
            ->add('save', SubmitType::class, ['label' => 'sylius_order_note.ui.save_note'])
            ->add(
                'delete',
                SubmitType::class,
                [
                'label' => 'sylius_order_note.ui.delete_note',
                'validation_groups' => false,
                ],
            );
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
            'data_class' => OrderNoteData::class,
            'csrf_protection' => true,
            'csrf_token_id' => 'sylius_order_note',
            ],
        );
    }

    #[Override]
    public function getBlockPrefix(): string
    {
        return 'sylius_order_note';
    }
}
