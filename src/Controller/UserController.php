<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class UserController extends AbstractController
{
    private $em;
    private $passwordHasher;

    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/useradd', name: 'app_useradd')]
    public function addformuser(Request $request, ManagerRegistry $m): Response
    {
        $em = $m->getManager();
        $utilisateur = new Utilisateur();

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($utilisateur);
            $em->flush();

            return $this->redirectToRoute('app_useradd');
        }

        return $this->render('user/user_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/usersshow', name: 'app_users_show')]
    public function showUsers(UtilisateurRepository $repo): Response
    {
        $users = $repo->findAll();

        return $this->render('user/users_show.html.twig', [
            'users' => $users,
        ]);
    }
    #[Route('/user/edit/{id}', name: 'app_user_edit')]
    public function updateUser($id, Request $request, ManagerRegistry $m, UtilisateurRepository $repo): Response
    {
        $em = $m->getManager();
        $utilisateur = $repo->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException("Utilisateur introuvable pour l'ID $id");
        }

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); // persist pas nécessaire pour une entité existante

            return $this->redirectToRoute('app_users_show');
        }

        return $this->render('user/user_edit.html.twig', [
            'form' => $form->createView(),
            'utilisateur' => $utilisateur,
        ]);
    }
    #[Route('/user/delete/{id}', name: 'app_user_delete')]
public function deleteUser($id, ManagerRegistry $m, UtilisateurRepository $repo): Response
{
    $em = $m->getManager();
    $utilisateur = $repo->find($id);

    if (!$utilisateur) {
        throw $this->createNotFoundException("Utilisateur introuvable pour l'ID $id");
    }

    // Supprimer l'utilisateur
    $em->remove($utilisateur);
    $em->flush();

    // Redirection vers la liste
    return $this->redirectToRoute('app_users_show');
}

public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
{
    $this->em = $em;
    $this->passwordHasher = $passwordHasher;
}
#[Route('/admin/hash-password/{id}', name: 'hash_password')]
public function hashPasswordForExistingUser($id)
{
    $user = $this->em->getRepository(Utilisateur::class)->find($id);

    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé');
    }

    // Hasher le mot de passe que tu veux
    $hashed = $this->passwordHasher->hashPassword($user, 'bbbbb'); // mot de passe voulu
    $user->setMotDePasse($hashed);
    $this->em->flush();

    return $this->json([
        'message' => 'Mot de passe hashé pour '.$user->getEmail()
    ]);
}


}
