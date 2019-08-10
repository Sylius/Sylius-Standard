<?php


namespace App\Form\Extension;


use Symfony\Component\Form\AbstractTypeExtension;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerProfileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductType extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Adding new fields works just like in the parent form type.
            ->add('secondaryPhoneNumber', TextType::class, [
                'required' => false,
                'label' => 'app.form.customer.secondary_phone_number',
            ]);

    }

    public static function getExtendedTypes(): iterable
    {
        return [CustomerProfileType::class];
    }
}
