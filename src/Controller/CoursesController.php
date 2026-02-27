<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CoursRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Cours;
use App\Form\CoursesType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class CoursesController extends AbstractController
{

    #[Route('/admin/courses', name: 'app_coursesback')]
    public function listCourses(CoursRepository $coursRepository): Response
    {
        $listcours = $coursRepository->findAll();
        
        return $this->render('courses/coursesback.html.twig', [
            'listcours' => $listcours,
        ]);
    }
    

    #[Route('/showcourses', name: 'app_showcourses')]
    public function showcourses(CoursRepository $coursrepo): Response
    {
        $a = $coursrepo->findAll();
        
        return $this->render('courses/courses.html.twig', [
            'listcours' => $a,
        ]);
    }

    #[Route('/addcours', name: 'app_addcours')]
    public function addcours(Request $req, ManagerRegistry $m, SluggerInterface $slugger): Response
    {
        $em = $m->getManager();
        $cours = new Cours();
        $form = $this->createForm(CoursesType::class, $cours);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/courses',
                        $newFilename
                    );
                    $cours->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading image');
                }
            }

            $em->persist($cours);
            $em->flush();
            return $this->redirectToRoute('app_showcourses');
        }
        
        return $this->render('courses/addcourses.html.twig', [
            'f' => $form,
        ]);
    }

    #[Route('/deletecours/{id}', name: 'app_deletecours')]
    public function deletecours($id, ManagerRegistry $m, CoursRepository $coursrepo): Response
    {
        $em = $m->getManager();
        $del = $coursrepo->find($id);
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('app_showcourses');
    }

    #[Route('/updatecours/{id}', name: 'app_updatecours')]
    public function updatecours($id, Request $req, ManagerRegistry $m, CoursRepository $coursrepo, SluggerInterface $slugger): Response
    {
        $em = $m->getManager();
        $cours = $coursrepo->find($id);
        $form = $this->createForm(CoursesType::class, $cours);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/courses',
                        $newFilename
                    );
                    $cours->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading image');
                }
            }

            $em->flush();  
            return $this->redirectToRoute('app_showcourses');
        }
        
        return $this->render('courses/updatecourses.html.twig', [
            'f' => $form,
        ]);
    }

    #[Route('/showcoursesback', name: 'app_showcoursesback')]
    public function showcoursesback(CoursRepository $coursrepo): Response
    {
        $a = $coursrepo->findAll();
        
        return $this->render('courses/coursesback.html.twig', [
            'listcours' => $a,
        ]);
    }

    #[Route('/addcoursback', name: 'app_addcoursback')]
    public function addcoursback(Request $req, ManagerRegistry $m, SluggerInterface $slugger): Response
    {
        $em = $m->getManager();
        $cours = new Cours();
        $form = $this->createForm(CoursesType::class, $cours);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/courses',
                        $newFilename
                    );
                    $cours->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading image');
                }
            }

            $em->persist($cours);
            $em->flush();
            return $this->redirectToRoute('app_showcoursesback');
        }
        
        return $this->render('courses/addcoursesback.html.twig', [
            'f' => $form,
        ]);
    }

    #[Route('/deletecoursback/{id}', name: 'app_deletecoursback')]
    public function deletecoursback($id, ManagerRegistry $m, CoursRepository $coursrepo): Response
    {
        $em = $m->getManager();
        $del = $coursrepo->find($id);
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('app_showcoursesback');
    }

    #[Route('/updatecoursback/{id}', name: 'app_updatecoursback')]
    public function updatecoursback($id, Request $req, ManagerRegistry $m, CoursRepository $coursrepo, SluggerInterface $slugger): Response
    {
        $em = $m->getManager();
        $cours = $coursrepo->find($id);
        $form = $this->createForm(CoursesType::class, $cours);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid()) {
            // Handle file upload
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/courses',
                        $newFilename
                    );
                    $cours->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Error uploading image');
                }
            }

            $em->flush();
            return $this->redirectToRoute('app_showcoursesback');
        }
        
        return $this->render('courses/updatecoursesback.html.twig', [
            'f' => $form,
        ]);
    }

}