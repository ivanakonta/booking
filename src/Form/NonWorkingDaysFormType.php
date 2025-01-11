<?php

namespace App\Form;

use App\Entity\NonWorkingDays;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class NonWorkingDaysFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', TypeTextType::class, [
            'constraints' => [
                new Assert\NotBlank(['message' => 'Datum ne može biti prazan']),
            ],
        ])
        ->add('description', TypeTextType::class, [
            'constraints' => [
                new Assert\NotBlank(['message' => 'Opis ne može biti prazan']),
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'Opis ne može biti duži od {{ limit }} karaktera.',
                ]),
                // Add more constraints as needed
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NonWorkingDays::class,
        ]);
    }
}
