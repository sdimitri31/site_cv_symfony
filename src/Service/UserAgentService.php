<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class UserAgentService
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getUserAgent(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        return $request ? $request->headers->get('User-Agent') : null;
    }
}