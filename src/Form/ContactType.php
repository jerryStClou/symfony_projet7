<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, ['attr' => ['class' => 'styleCRUD']])
            ->add('lastName', null, ['attr' => ['class' => 'styleCRUD']])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Length([
                        'min' => 6
                    ])

                ], 'attr' => ['class' => 'styleCRUD']

            ])
            ->add('description', null, ['attr' => ['class' => 'styleCRUD']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
