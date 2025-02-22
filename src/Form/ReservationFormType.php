<?php

namespace App\Form;

use App\Entity\Guest;
use App\Entity\Reservation;
use App\Entity\Restaurant;
use App\Entity\TimeSlot;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $restaurant = $options['restaurant'];
        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control', 'min' => (new \DateTime())->format('Y-m-d')],
                'data' => $options['data']->getDate() ?: new \DateTime(),
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'Datum ne može biti u prošlosti.',
                    ]),
                ],
            ])
            ->add('numberOfPersons')
            ->add('restaurant', EntityType::class, [
                'class' => Restaurant::class,
                'choice_label' => 'id',
            ])
            ->add('guest', GuestFormType::class)
            ->add('time', EntityType::class, [
                'class' => TimeSlot::class,
                'choice_label' => 'time',
                'placeholder' => 'Select time option',
                'required' => true,
                'query_builder' => function (EntityRepository $er) use ($restaurant) {
                    // Filter by selected restaurant
                    return $er->createQueryBuilder('v')
                        ->where('v.restaurant = :restaurant')
                        ->setParameter('restaurant', $restaurant)
                        ->orderBy('v.time', 'ASC'); // Sortiranje po vremenu
                },
            ])
        ; 
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
            'restaurant' => null,
        ]);
    }
}
