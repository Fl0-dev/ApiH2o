<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Something;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\NoRecipient;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Something("hello");
 */
class SendingController extends AbstractController
{

    #[Route('/sending', name: 'app_sending')]
    public function sending(NotifierInterface $notifier): Response
    {
        $content = "Hey Papa !";
        $notification = (new Notification('La p%#ç de sa m@#&', ['chat/discord']))
            ->content($content);

        $user = new User();
        $user->setEmail('supa-dupafly@live.fr');
        $user->setPhoneNumber(0606060606);

        $recipient = new Recipient(
            $user->getEmail(),
            $user->getPhoneNumber()
        );

        $notifier->send($notification, new NoRecipient());

        return $this->render('sending/confirmation.html.twig', [
            'user' => $user,
            'notification' => $notification,
        ]);
    }
}
