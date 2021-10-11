<?php

namespace App\Form\Type\CatalogPromotionScope;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ByPhraseScopeConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('phrase', TextType::class, [
            'label' => 'Phrase',
            'constraints' => [
                new NotBlank(['groups' => ['sylius']]),
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_catalog_promotion_scope_by_phrase_configuration';
    }
}
