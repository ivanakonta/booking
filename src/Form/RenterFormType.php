<?php

namespace App\Form;

use App\Entity\Korisnik;
use App\Entity\Renter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RenterFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('adresa')
            ->add('email')
            ->add('logo', FileType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M', // Maximum file size allowed
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/jpg', // Allow JPEG files
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, JPG, PNG, GIF)',
                    ]),
                ],
            ])
            ->add('mobitel')
            ->add('isActive')
            ->add('monriPublicKey')
            ->add('monriSecretKey')
            ->add('googleCalendar')
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('modified_at', null, [
                'widget' => 'single_text',
            ])
            ->add('days', ChoiceType::class, [
                'label' => 'Working Days',
                'choices' => [
                    'Ponedjeljak' => 'Ponedjeljak',
                    'Utorak' => 'Utorak',
                    'Srijeda' => 'Srijeda',
                    'Četvrtak' => 'Četvrtak',
                    'Petak' => 'Petak',
                    'Subota' => 'Subota',
                    'Nedjelja' => 'Nedjelja',
                ],
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Renter::class,
        ]);
    }
}
