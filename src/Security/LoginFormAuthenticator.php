<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        return new RedirectResponse($this->router->generate('expense_list'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new RedirectResponse($this->router->generate('login'));
    }

    public function authenticate(Request $request): Passport
    {
        // Get username and password from the request
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        // If username or password is missing, throw an exception
        if (empty($username) || empty($password)) {
            throw new CustomUserMessageAuthenticationException('Username or password cannot be empty.');
        }

        // Return a Passport object with the UserBadge (for the user) and PasswordCredentials (for the password)
        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password)
        );
    }
    protected function getLoginUrl(Request $request): string
    {
        // Return the route to the login page
        return $this->router->generate('login');
    }

    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'login' && $request->isMethod('POST');
    }
}
