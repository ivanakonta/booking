<?php

namespace App\Form;

use App\Entity\Korisnik;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class KorisnikFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $restaurant = $options['restaurant']; // Get the selected restaurant passed to the form

        $builder
            ->add('username', null, [
                'label' => 'Korisničko ime', // Label for username
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Promijeni lozinku', // Label for password
                'required' => false, // Make password optional
                'mapped' => false,   // This ensures the field is not mapped directly to the entity
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email adresa', // Label for email
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Menadžer' => 'ROLE_MANAGER',
                    'Zaposlenik' => 'ROLE_USER',
                ],
                'expanded' => true,  // Renders as checkboxes
                'multiple' => true,  // Allows multiple roles to be selected
                'label' => false,
            ])
            ->add('created_at', null, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Kreiran',

            ])
            ->add('modified_at', null, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Ažuriran',

            ])
            ->add('isActive')
            ->add('firstName')
            ->add('lastName')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Korisnik::class,
            'restaurant' => null, // Define the restaurant option
        ]);
    }
}
