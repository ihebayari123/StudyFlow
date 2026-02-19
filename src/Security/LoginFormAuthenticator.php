<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private EntityManagerInterface $entityManager;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');
        $password = $request->request->get('password', '');
        $csrfToken = $request->request->get('_csrf_token');

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrfToken),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $user = $token->getUser();

        if (!$user instanceof \App\Entity\Utilisateur) {
            return new RedirectResponse('/');
        }

        // Mise à jour des statistiques de connexion
        $user->setLastLogin(new \DateTime());
        $user->setLoginFrequency($user->getLoginFrequency() + 1);
        $user->setFailedLoginAttempts(0);

        $this->entityManager->flush();

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

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
{
    // Récupérer l'email depuis la requête
    $email = $request->request->get('email', '');
    
    if ($email) {
        try {
            $user = $this->entityManager->getRepository(\App\Entity\Utilisateur::class)
                ->findOneBy(['email' => $email]);
                
            if ($user) {
                $user->setFailedLoginAttempts($user->getFailedLoginAttempts() + 1);
                $this->entityManager->flush();
                
                // Log pour debug
                error_log("🔴 Failed login attempt recorded for $email - New total: " . $user->getFailedLoginAttempts());
            } else {
                error_log("🔴 User not found for email: $email");
            }
        } catch (\Exception $e) {
            error_log("🔴 Error recording failed login: " . $e->getMessage());
        }
    }

    // Rediriger vers la page de login avec message d'erreur
    $request->getSession()->set('_security.last_error', $exception);
    
    return new RedirectResponse($this->urlGenerator->generate('app_login'));
}
}
