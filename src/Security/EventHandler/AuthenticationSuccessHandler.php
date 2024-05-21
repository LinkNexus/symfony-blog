<?php

namespace App\Security\EventHandler;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

readonly class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private UrlGeneratorInterface $router;
    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $session = new Session();
        $session->getFlashBag()->add('success', 'You are logged in to Nexus');
        return new RedirectResponse($this->router->generate('app_home'));
    }
}