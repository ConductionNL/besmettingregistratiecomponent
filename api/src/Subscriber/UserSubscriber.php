<?php

namespace App\Subscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\AuditTrail;
use App\Entity\User;
use App\Service\NLXLogService;
use Doctrine\ORM\EntityManagerInterface;
use Service\UserService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class UserSubscriber implements EventSubscriberInterface
{
    private $params;
    private $em;
    private $serializer;
    private $userService;

    public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, SerializerInterface $serializer, UserService $userService)
    {
        $this->params = $params;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->userService = $userService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['User', EventPriorities::PRE_SERIALIZE],
        ];
    }

    public function Audittrail(ViewEvent $event)
    {
        $method = $event->getRequest()->getMethod();
        $result = $event->getControllerResult();

        // Only do somthing if we are on te log route and the entity is logable
        if ($method != 'PUT' && $method != 'POST' && $method != 'PATCH' && !($result instanceof User)) {
            return;
        }

        // Lets get the rest of the data
        $contentType = $event->getRequest()->headers->get('accept');
        if (!$contentType) {
            $contentType = $event->getRequest()->headers->get('Accept');
        }
        switch ($contentType) {
            case 'application/json':
                $renderType = 'json';
                break;
            case 'application/ld+json':
                $renderType = 'jsonld';
                break;
            case 'application/hal+json':
                $renderType = 'jsonhal';
                break;
            default:
                $contentType = 'application/json';
                $renderType = 'json';
        }
        $result = $this->userService->validateUser($result);

        $response = $this->serializer->serialize(
            $result,
            $renderType,
            ['enable_max_depth'=> true]
        );

        // Creating a response
        $response = new Response(
            $response,
            Response::HTTP_CREATED,
            ['content-type' => $contentType]
        );

        $event->setResponse($response);
    }
}
