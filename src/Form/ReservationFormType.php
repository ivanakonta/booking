<?php

namespace App\Form;

use App\Entity\Guest;
use App\Entity\Reservation;
use App\Entity\Restaurant;
use App\Entity\TimeSlot;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date')
            ->add('numberOfPersons')
            ->add('restaurant', EntityType::class, [
                'class' => Restaurant::class,
                'choice_label' => 'id',
            ])
            ->add('guest', EntityType::class, [
                'class' => Guest::class,
                'choice_label' => 'id',
            ])
            ->add('time', EntityType::class, [
                'class' => TimeSlot::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
