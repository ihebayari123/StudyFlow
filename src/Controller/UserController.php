<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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
    public function addformuser(Request $request, ManagerRegistry $m, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $m->getManager();
        $utilisateur = new Utilisateur();

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('motDePasse')->getData();
            $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
            $utilisateur->setMotDePasse($hashedPassword);

            $em->persist($utilisateur);
            $em->flush();

            return $this->redirectToRoute('app_users_show');
        }

        return $this->render('user/user_add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/usersshow', name: 'app_users_show')]
    public function showUsers(UtilisateurRepository $repo, Request $request): Response
    {
        // Récupération des paramètres de tri
        $sortBy = $request->query->get('sort', 'id');
        $order = $request->query->get('order', 'ASC');
        
        // Récupération du terme de recherche
        $searchTerm = $request->query->get('search', '');
        
        // Validation des champs de tri autorisés
        $allowedSort = ['id', 'nom', 'prenom', 'email', 'role', 'statutCompte'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'id';
        }
        
        // Construction de la requête avec recherche
        $qb = $repo->createQueryBuilder('u');
        
        if ($searchTerm) {
            $qb->where('u.nom LIKE :search')
               ->orWhere('u.prenom LIKE :search')
               ->orWhere('u.email LIKE :search')
               ->setParameter('search', '%' . $searchTerm . '%');
        }
        
        // Application du tri
        $qb->orderBy('u.' . $sortBy, $order);
        
        $users = $qb->getQuery()->getResult();

        return $this->render('user/users_show.html.twig', [
            'users' => $users,
            'currentSort' => $sortBy,
            'currentOrder' => $order,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/user/edit/{id}', name: 'app_user_edit')]
    public function updateUser($id, Request $request, ManagerRegistry $m, UtilisateurRepository $repo, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $m->getManager();
        $utilisateur = $repo->find($id);

        if (!$utilisateur) {
            throw $this->createNotFoundException("Utilisateur introuvable pour l'ID $id");
        }

        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('motDePasse')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($utilisateur, $plainPassword);
                $utilisateur->setMotDePasse($hashedPassword);
            }
            
            $em->flush();

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

    $conn = $em->getConnection();
    
    try {
        // 1. Récupérer tous les IDs des cours de l'utilisateur
        $cours = $conn->fetchAllAssociative('SELECT id FROM cours WHERE user_id = ?', [$id]);
        $coursIds = array_column($cours, 'id');
        
        if (!empty($coursIds)) {
            $placeholders = implode(',', array_fill(0, count($coursIds), '?'));
            
            // 2. Récupérer tous les IDs des quizzes de ces cours
            $quizzes = $conn->fetchAllAssociative("SELECT id FROM quiz WHERE course_id IN ($placeholders)", $coursIds);
            $quizIds = array_column($quizzes, 'id');
            
            if (!empty($quizIds)) {
                $quizPlaceholders = implode(',', array_fill(0, count($quizIds), '?'));
                
                // 3. Supprimer les questions liées à ces quizzes
                $conn->executeStatement("DELETE FROM question WHERE quiz_id IN ($quizPlaceholders)", $quizIds);
                
                // 4. Supprimer les quizzes
                $conn->executeStatement("DELETE FROM quiz WHERE id IN ($quizPlaceholders)", $quizIds);
            }
            
            // 5. Supprimer les cours
            $conn->executeStatement("DELETE FROM cours WHERE id IN ($placeholders)", $coursIds);
        }
        
        // 6. Supprimer les autres entités liées directement à l'utilisateur
        $conn->executeStatement('DELETE FROM stress_survey WHERE user_id = ?', [$id]);
        $conn->executeStatement('DELETE FROM produit WHERE user_id = ?', [$id]);
        $conn->executeStatement('DELETE FROM event WHERE user_id = ?', [$id]);
        
        // 7. Enfin, supprimer l'utilisateur
        $conn->executeStatement('DELETE FROM utilisateur WHERE id = ?', [$id]);
        
        $this->addFlash('success', 'Utilisateur et toutes ses données associées (cours, quizzes, questions, etc.) supprimés avec succès !');
        
    } catch (\Exception $e) {
        $conn->executeStatement('ROLLBACK');
        $this->addFlash('error', 'Erreur : ' . $e->getMessage());
    }

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

        $hashed = $this->passwordHasher->hashPassword($user, 'oussema123');
        $user->setMotDePasse($hashed);
        $this->em->flush();

        return $this->json([
            'message' => 'Mot de passe hashé pour ' . $user->getEmail()
        ]);
    }
}