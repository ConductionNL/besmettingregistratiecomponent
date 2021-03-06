<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private $em;
    private $commongroundService;

    public function __construct(EntityManagerInterface $em, CommonGroundService $commonGroundService)
    {
        $this->em = $em;
        $this->commongroundService = $commonGroundService;
    }

    public function validateUser(User $user): User
    {
        if ($user->getConfirmed() && !$user->getInfected()) {
            $user->setInfected(true);
        }
        if (!$user->getInfected() || $user->getConfirmed()) {
            if ($user->getContact()) {
                $this->commongroundService->deleteResource($user->getContact());
            }
            $user->setContact(null);
        }
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
