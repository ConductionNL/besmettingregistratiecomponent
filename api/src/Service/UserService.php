<?php


namespace Service;


use App\Entity\Contact;
use App\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class UserService
{
    private $em;
    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    public function validateUser(User $user) : User
    {
        if($user->getConfirmed() && !$user->getInfected()){
            $user->setInfected(true);
        }
        if(!$user->getInfected() || $user->getConfirmed()){
            $user->setContact(null);
        }
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
