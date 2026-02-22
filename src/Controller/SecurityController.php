<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Form\FormFactoryInterface; // ← IMPORTANT
use App\Form\LoginType;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        FormFactoryInterface $formFactory // ← INJECTE LE FORM FACTORY
    ): Response {
        // ✅ Créer le formulaire sans nom via le formFactory
        $form = $formFactory->createNamed('', LoginType::class);
        
        // Ne pas handleRequest ici, c'est l'authenticator qui le fera

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login/success', name: 'login_success')]
    public function chooseArea(): Response
    {
        return $this->render('security/choose_area.html.twig');
    }
}