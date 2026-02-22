<?php

namespace App\Controller;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Medecin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MedecinRepository;
use App\Form\MedecinType;
final class MedecinController extends AbstractController
{
    #[Route('/medecin', name: 'app_medecin')]
    public function index(): Response
    {
        return $this->render('medecin/index.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }















     #[Route('/showmedecin', name: 'app_showmedecin')]
    public function showsmedecin(MedecinRepository $bookrepo, Request $request): Response
    {
        $search = $request->query->get('search', '');
        $sort = $request->query->get('sort', 'nom');
        $order = $request->query->get('order', 'ASC');
        
        if (!empty($search)) {
            $a = $bookrepo->searchByNomOrPrenom($search, $sort, $order);
        } else {
            $a = $bookrepo->findBy([], [$sort => $order]);
        }
        
        return $this->render('medecin/showmedecin.html.twig', [
            'listmedecin' => $a,
            'search' => $search,
            'sort' => $sort,
            'order' => $order,
        ]);
    }


#[Route('/delete_medecin/{id}', name: 'app_delete_medecin')]
    public function delete_medecin($id, ManagerRegistry $m, MedecinRepository $authorrepo): Response
    {
        $em = $m->getManager();
        $del = $authorrepo->find($id);
        
        if (!$del) {
            throw $this->createNotFoundException('Médecin non trouvé');
        }
        
        $em->remove($del);
        $em->flush();
        
        $this->addFlash('success', 'Médecin supprimé avec succès');
        return $this->redirectToRoute('app_showmedecin');
    }
#[Route('/add_medecin', name: 'app_add_medecin')]
    public function addMedecin(ManagerRegistry $m, Request $request): Response
    {
        $em = $m->getManager();
        $stresse = new Medecin();
        $form = $this->createForm(MedecinType::class, $stresse);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($stresse);
            $em->flush();
            return $this->redirectToRoute('app_showmedecin');
        }
        
        return $this->render('medecin/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/updateformmedecin/{id}', name: 'app_updatemedecin')]
    public function updateformmedecin($id, Request $req, ManagerRegistry $m, MedecinRepository $authorrepo): Response
    {
        $em = $m->getManager();
        $author = $authorrepo->find($id);

        if (!$author) {
            throw $this->createNotFoundException('Medecin non trouvé');
        }

        $form = $this->createForm(MedecinType::class, $author);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_showmedecin');
        }
        return $this->render('medecin/updateformmedecin.html.twig', [
            'f' => $form,
        ]);
    }

 
 
    }







