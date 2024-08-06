<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Response;
use App\Service\MaintenanceService;

class MaintenanceListener
{
    private $maintenanceService;
    private $security;

    public function __construct(MaintenanceService $maintenanceService, Security $security)
    {
        $this->maintenanceService = $maintenanceService;
        $this->security = $security;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $user = $this->security->getUser();

        if ($this->maintenanceService->isMaintenanceMode()) {
            // Allow access to login routes
            if ($this->isAdminRoute($request) || $user && $this->isAdmin($user)) {
                return;
            }

            // Redirect to maintenance page if not allowed
            $event->setResponse(new Response(
                $this->renderMaintenancePage(),
                Response::HTTP_SERVICE_UNAVAILABLE
            ));
        }
    }

    private function isAdminRoute($request): bool
    {
        // Allow access to admin login routes
        return in_array($request->attributes->get('_route'), ['app_login', 'app_logout']);
    }

    private function isAdmin($user): bool
    {
        return $user && in_array('ROLE_ADMIN', $user->getRoles());
    }

    private function renderMaintenancePage(): string
    {
        return '<html><body><h1>Site en maintenance</h1><p>Nous reviendrons bientôt.</p></body></html>';
    }
}
