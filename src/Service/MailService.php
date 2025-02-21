<?php
namespace App\Service;
use App\Entity\Renter;
use App\Entity\Reservation;
use App\Entity\Restaurant;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
/**
 * Service for handling email notifications related to reservations.
 */
class MailService
{
    /**
     * @var MailerInterface Symfony Mailer service.
     */
    private $mailer;
    /**
     * MailService constructor.
     *
     * @param MailerInterface $mailer Symfony mailer interface for sending emails.
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    /**
     * Sends a templated email.
     *
     * @param string $from Sender email address.
     * @param string $to Recipient email address.
     * @param string $subject Email subject.
     * @param string $template Twig template path for the email.
     * @param array $context Context variables for the template.
     *
     * @throws TransportExceptionInterface
     */
    private function sendTemplatedEmail(string $from, string $to, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);
        $this->mailer->send($email);
    }
    /**
     * Sends a confirmation email to the guest for a reservation.
     *
     * @param Reservation $reservation The reservation entity.
     */
    public function sendConfirmedReservationEmailToGuest(Reservation $reservation): void
    {
        $restaurant = $reservation->getRestaurant();
        $guest = $reservation->getGuest();
        $from = sprintf('%s <ivana.konta99@gmail.com>', $restaurant->getName());
        $to = $guest->getEmail();
        $subject = 'Your Reservation is Confirmed';
        $template = 'mail/confirmed_reservation_to_guest.html.twig';
        $context = [
            'pageTitle' => $subject,
            'restaurant' => $restaurant,
            'reservation' => $reservation,
            'guest' => $guest,
            'reservationDate' => $reservation->getDate()->format('F j, Y'),
            'reservationTime' => $reservation->getTime()->getTime(),
            'reseervationNumberOfPersons' => $reservation->getNumberOfPersons(),
            'token' => $this->getToken($reservation, $restaurant),
        ];
        $this->sendTemplatedEmail($from, $to, $subject, $template, $context);
    }
    /**
     * Sends a cancellation email to the guest.
     *
     * @param Reservation $reservation The reservation entity.
     */
    public function sendCanceledReservationEmailToGuest(Reservation $reservation): void
    {
        $restaurant = $reservation->getRestaurant();
        $guest = $reservation->getGuest();
        $from = sprintf('%s <ivana.konta99@gmail.com>', $restaurant->getName());
        $to = $guest->getEmail();
        $subject = 'Your Reservation is Cancelled';
        $template = 'mail/canceled_reservation_to_guest.html.twig';
        $context = [
            'pageTitle' => $subject,
            'reservation' => $reservation,
            'guest' => $guest,
            'restaurant' => $restaurant,
            'reservationDate' => $reservation->getDate()->format('F j, Y'),
            'reservationTime' => $reservation->getTime()->getTime(),
            'reseervationNumberOfPersons' => $reservation->getNumberOfPersons(),
            'token' => $this->getToken($reservation, $restaurant),
        ];
        $this->sendTemplatedEmail($from, $to, $subject, $template, $context);
    }
    /**
     * Sends a edited email to the guest.
     *
     * @param Reservation $reservation The reservation entity.
     */
    public function sendEditedReservationEmailToGuest(Reservation $reservation): void
    {
        $restaurant = $reservation->getRestaurant();
        $guest = $reservation->getGuest();
        $from = sprintf('%s <ivana.konta99@gmail.com>', $restaurant->getName());
        $to = $guest->getEmail();
        $subject = 'Your Reservation is Edited';
        $template = 'mail/edited_reservation_to_guest.html.twig';
        $context = [
            'pageTitle' => $subject,
            'reservation' => $reservation,
            'guest' => $guest,
            'restaurant' => $restaurant,
            'reservationDate' => $reservation->getDate()->format('F j, Y'),
            'reservationTime' => $reservation->getTime()->getTime(),
            'reseervationNumberOfPersons' => $reservation->getNumberOfPersons(),
            'token' => $this->getToken($reservation, $restaurant),
        ];
        $this->sendTemplatedEmail($from, $to, $subject, $template, $context);
    }
    /**
     * Sends a finished email to the guest.
     *
     * @param Reservation $reservation The reservation entity.
     */
    public function sendFinishedReservationEmailToGuest(Reservation $reservation): void
    {
        $restaurant = $reservation->getRestaurant();
        $guest = $reservation->getGuest();
        $from = sprintf('%s <ivana.konta99@gmail.com>', $restaurant->getName());
        $to = $guest->getEmail();
        $subject = 'Your Reservation is Finished';
        $template = 'mail/finished_reservation_to_guest.html.twig';
        $context = [
            'pageTitle' => $subject,
            'reservation' => $reservation,
            'guest' => $guest,
            'restaurant' => $restaurant,
            'reservationDate' => $reservation->getDate()->format('F j, Y'),
            'reservationTime' => $reservation->getTime()->getTime(),
            'reseervationNumberOfPersons' => $reservation->getNumberOfPersons(),
            'token' => $this->getToken($reservation, $restaurant),
        ];
        $this->sendTemplatedEmail($from, $to, $subject, $template, $context);
    }
    /**
     * Sends a confirmation email to the renter when a new reservation is made.
     *
     * @param Reservation $reservation The reservation entity.
     */
    public function sendConfirmedReservationEmailToRestaurant(Reservation $reservation): void
    {
        $restaurant = $reservation->getRestaurant();
        $from = sprintf('%s <ivana.konta99@gmail.com>', $restaurant->getName());
        $to = $restaurant->getEmail();
        $subject = 'New Reservation';
        $template = 'mail/confirmed_reservation_to_restaurant.html.twig';
        $context = [
            'pageTitle' => $subject,
            'restaurant' => $restaurant,
            'reservation' => $reservation,
            'guest' => $reservation->getGuest(),
            'reservationDate' => $reservation->getDate()->format('F j, Y'),
            'reservationTime' => $reservation->getTime()->getTime(),
            'reseervationNumberOfPersons' => $reservation->getNumberOfPersons(),
        ];
        $this->sendTemplatedEmail($from, $to, $subject, $template, $context);
    }
    /**
     * Sends a cancellation email to the renter.
     *
     * @param Reservation $reservation The reservation entity.
     */
    public function sendCanceledReservationEmailToRestaurant(Reservation $reservation): void
    {
        $restaurant = $reservation->getRestaurant();
        $from = sprintf('%s <ivana.konta99@gmail.com>', $restaurant->getName());
        $to = $restaurant->getEmail();
        $subject = 'Reservation is Cancelled';
        $template = 'mail/canceled_reservation_to_restaurant.html.twig';
        $context = [
            'pageTitle' => $subject,
            'reservation' => $reservation,
            'guest' => $reservation->getGuest(),
            'restaurant' => $restaurant,
            'reservationDate' => $reservation->getDate()->format('F j, Y'),
            'reservationTime' => $reservation->getTime()->getTime(),
            'reseervationNumberOfPersons' => $reservation->getNumberOfPersons(),
        ];
        $this->sendTemplatedEmail($from, $to, $subject, $template, $context);
    }
    /**
     * Generates a unique token for the reservation.
     *
     * @param Reservation $reservation The reservation entity.
     * @param Restaurant $restaurant The renter entity.
     *
     * @return string The generated hash token.
     */
    private function getToken(Reservation $reservation, Restaurant $restaurant): string
    {
        return hash('sha256', sprintf('%s%s', $reservation->getId(), $restaurant->getEmail()));
    }
}