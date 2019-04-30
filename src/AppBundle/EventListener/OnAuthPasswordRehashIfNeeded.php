<?php

namespace AppBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Class OnAuthPasswordRehashIfNeeded
 * @package AppBundle\EventListener
 */
class OnAuthPasswordRehashIfNeeded
{
    /**
     * @var int
     */
    private $cost;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * OnAuthPasswordRehashIfNeeded constructor.
     * @param int $cost
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        int $cost,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->cost = $cost;
    }

    /**
     * On authentication checks if user's password needs rehash in case of bcrypt cost change
     * WARNING : Will rehash password even if new cost is lower than current hash cost
     *
     * @param InteractiveLoginEvent $event
     */
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $options = ["cost" => $this->cost];
        $currentHashedPassword = $user->getPassword();

        if (password_needs_rehash($currentHashedPassword, PASSWORD_BCRYPT, $options)) {
            $em = $this->entityManager;
            $plainPassword = $event->getRequest()->request->get('_password');

            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, $plainPassword)
            );

            $em->flush();
        }
    }
}
