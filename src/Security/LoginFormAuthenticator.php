<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
{
    $email = $request->request->get('email', '');
    $password = $request->request->get('password', '');
    $csrfToken = $request->request->get('_csrf_token');

    // Debug: log credentials (remove after fixing)
    error_log("Login attempt - email: $email, password length: ".strlen($password));

    return new Passport(
        new UserBadge($email),
        new PasswordCredentials($password),
        [
            new CsrfTokenBadge('authenticate', $csrfToken),
            new RememberMeBadge(),
        ]
    );
}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // If there's a target path (user tried to access a protected page), redirect there
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();
        
        if (!$user instanceof \App\Entity\Utilisateur) {
            return new RedirectResponse('/');
        }
        
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('app_admin'));
        }

        if (in_array('ROLE_ENSEIGNANT', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('login_success'));
        }

        if (in_array('ROLE_ETUDIANT', $roles, true)) {
            return new RedirectResponse($this->urlGenerator->generate('app_home'));
        }

        return new RedirectResponse($this->urlGenerator->generate('app_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}