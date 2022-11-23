<?php
declare(strict_types=1);

namespace App\Form\Type\Color;

use App\Type\Color\Color;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer((new ColorDataTransformer()));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => array_flip(Color::CONST_ARRAY),
                'required' => false,
            ]
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
