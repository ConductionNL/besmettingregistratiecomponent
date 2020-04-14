<?php


namespace Service;


use App\Entity\Contact;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class UserService
{

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }
    public function createContacts(ArrayCollection $users, User $user, \DateTimeInterface $dateCreated){
        $result = new ArrayCollection();
        foreach($users as $secondUser){
            $contact = new Contact();
            $contact->setDateCreated($dateCreated);
            $contact->addUser($user);
            $contact->addUser($secondUser);

            $this->em->persist($contact);
            $result->add($contact);
        }
        $this->em->flush();

        return $result;
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
    }
}
