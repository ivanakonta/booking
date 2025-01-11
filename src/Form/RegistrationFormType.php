<?php

namespace App\Form;

use App\Entity\Korisnik;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $renter = $options['renter']; // Get the selected Renter passed to the form

        $builder
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('username', TextType::class)
            ->add('isActive')
            ->add('password', PasswordType::class)
            ->add('email', TextType::class, [
                'required' => false, // Make email optional
            ])
            ->add('role', ChoiceType::class, [
                'choices'  => [
                    'Menadžer' => 'ROLE_MANAGER',
                    'Radnik' => 'ROLE_USER',
                ],
                'mapped' => false, // This ensures the field is not mapped to any entity property
                'label' => 'Registriraj kao',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Korisnik::class,
            'renter' => null, // Define the Renter option
        ]);
    }
}
