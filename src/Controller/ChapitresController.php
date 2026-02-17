<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Entity\Cours;
use App\Repository\CoursRepository;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChapitresController extends AbstractController
{
    #[Route('/chapitres', name: 'app_chapitres_index')]
    public function index(ChapitreRepository $chapitreRepository): Response
    {
        $chapitres = $chapitreRepository->findAll();
        
        return $this->render('chapitres/index.html.twig', [
            'controller_name' => 'ChapitresController',
            'chapitres' => $chapitres,
        ]);
    }

    #[Route('/admin/courses/{courseId}/chapters', name: 'app_chapitres')]
    public function showChapitresByCourse(
        int $courseId, 
        CoursRepository $coursRepository
    ): Response
    {
        $cours = $coursRepository->find($courseId);
        
        if (!$cours) {
            $this->addFlash('error', 'Course not found');
            return $this->redirectToRoute('app_coursesback');
        }

        $chapitres = $cours->getChapitres();

        return $this->render('chapitres/chapitre.html.twig', [
            'cours' => $cours,
            'chapitres' => $chapitres,
        ]);
    }

    #[Route('/admin/courses/{courseId}/chapters/add', name: 'app_addchapitre', methods: ['GET', 'POST'])]
    public function addChapitre(
        int $courseId, 
        Request $request,
        CoursRepository $coursRepository,
        EntityManagerInterface $em
    ): Response
    {
        $cours = $coursRepository->find($courseId);
        
        if (!$cours) {
            $this->addFlash('error', 'Course not found');
            return $this->redirectToRoute('app_coursesback');
        }

        if ($request->isMethod('POST')) {
            $chapitre = new Chapitre();
            $chapitre->setTitre($request->request->get('titre'));
            $chapitre->setContenu($request->request->get('contenu'));
            $chapitre->setOrdre($request->request->get('ordre'));
            $chapitre->setCourse($cours);

            $em->persist($chapitre);
            $em->flush();

            $this->addFlash('success', 'Chapter created successfully');
            return $this->redirectToRoute('app_chapitres', ['courseId' => $courseId]);
        }

        return $this->render('chapitres/add.html.twig', [
            'cours' => $cours,
        ]);
    }

    #[Route('/admin/chapters/{id}/edit', name: 'app_editchapitre', methods: ['GET', 'POST'])]
    public function editChapitre(
        int $id, 
        Request $request,
        ChapitreRepository $chapitreRepository,
        EntityManagerInterface $em
    ): Response
    {
        $chapitre = $chapitreRepository->find($id);
        
        if (!$chapitre) {
            $this->addFlash('error', 'Chapter not found');
            return $this->redirectToRoute('app_coursesback');
        }

        if ($request->isMethod('POST')) {
            $chapitre->setTitre($request->request->get('titre'));
            $chapitre->setContenu($request->request->get('contenu'));
            $chapitre->setOrdre($request->request->get('ordre'));

            $em->flush();

            $this->addFlash('success', 'Chapter updated successfully');
            return $this->redirectToRoute('app_chapitres', ['courseId' => $chapitre->getCourse()->getId()]);
        }

        return $this->render('chapitres/edit.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/admin/chapters/{id}/delete', name: 'app_deletechapitre')]
    public function deleteChapitre(
        int $id, 
        ChapitreRepository $chapitreRepository,
        EntityManagerInterface $em
    ): Response
    {
        $chapitre = $chapitreRepository->find($id);
        
        if (!$chapitre) {
            $this->addFlash('error', 'Chapter not found');
            return $this->redirectToRoute('app_coursesback');
        }

        $courseId = $chapitre->getCourse()->getId();
        
        $em->remove($chapitre);
        $em->flush();
        
        $this->addFlash('success', 'Chapter deleted successfully');
        return $this->redirectToRoute('app_chapitres', ['courseId' => $courseId]);
    }
}