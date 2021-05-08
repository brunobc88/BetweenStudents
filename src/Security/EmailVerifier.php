<?php

namespace App\Security;

use App\Entity\Sortie;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private $verifyEmailHelper;
    private $mailer;
    private $entityManager;

    public function __construct(VerifyEmailHelperInterface $helper, MailerInterface $mailer, EntityManagerInterface $manager)
    {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->entityManager = $manager;
    }

    public function sendEmailConfirmation(string $verifyEmailRouteName, UserInterface $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail()
        );

        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@noams88.fr', 'BetweenStudents'))
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('registration/confirmation_email.html.twig');

        $context = $email->getContext();
        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, UserInterface $user): void
    {
        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function sendEmailAnnulationSortie(User $participant, Sortie $sortie): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@noams88.fr', 'BetweenStudents'))
            ->to($participant->getEmail())
            ->subject('Annulation de la sortie ' . $sortie->getNom())
            ->htmlTemplate('sortie/annulation_email.html.twig');

        $context = $email->getContext();
        $context['sortie'] = $sortie;
        $context['user'] = $participant;

        $email->context($context);

        $this->mailer->send($email);
    }

    public function sendEmailUserEtatCompte(User $user)
    {
        if ($user->getActif()) {
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@noams88.fr', 'BetweenStudents'))
                ->to($user->getEmail())
                ->subject('Ré-activation de votre compte')
                ->htmlTemplate('user/etatCompte_email.html.twig');
        }
        else {
            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@noams88.fr', 'BetweenStudents'))
                ->to($user->getEmail())
                ->subject('Suspension de votre compte')
                ->htmlTemplate('user/etatCompte_email.html.twig');
        }

        $context = $email->getContext();
        $context['user'] = $user;

        $email->context($context);

        $this->mailer->send($email);
    }
}
