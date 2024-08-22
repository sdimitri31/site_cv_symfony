<?php

namespace App\Service;

use App\Entity\Visit;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VisitLogger implements EventSubscriberInterface
{
    private $entityManager;
    private $requestStack;
    private $security;
    private $userAgentService;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, Security $security, UserAgentService $userAgentService)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->userAgentService = $userAgentService;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $visitedURL = $this->getFormattedUrl($request->getUri());
        if ($visitedURL === '') {
            return;
        }

        $useragent = $this->userAgentService->getUserAgent();

        // Capture details
        $visit = new Visit();
        $visit->setIp($request->getClientIp());
        $visit->setUrl($visitedURL);
        $visit->setMethod($request->getMethod());
        $visit->setVisitedBy($this->getUserId());
        $visit->setUserAgent($useragent);

        try{
            $visit->setVisitedAt(new \DateTimeImmutable('now', new DateTimeZone('Europe/Paris')));
        }
        catch(\Exception $e){
            $visit->setVisitedAt(new \DateTimeImmutable());
        }

        // Persist visit
        $this->entityManager->persist($visit);
        $this->entityManager->flush();
    }

    private function getFormattedUrl(string $url): string
    {
        // Parse the URL and get the path
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';

        // Remove leading slashes and filter paths starting with '_'
        $path = ltrim($path, '/');
        if (str_starts_with($path, '_')) {
            return '';
        }

        return '/' . $path;
    }

    private function getUserId(): int
    {
        $user = $this->security->getUser();
        return $user ? $user->getId() : 0;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }
}
