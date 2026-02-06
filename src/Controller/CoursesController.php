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

final class CoursesController extends AbstractController
{
    

     #[Route('/showcourses', name: 'app_showcourses')]
    public function showcourses(CoursRepository $coursrepo): Response
    {
        $a=$coursrepo->findAll();
        //$a=$authorrepo->findbyusername();
       // $a=$authorrepo->trieDescUsername();
        //$a=$coursrepo->trieAcsUsername();
        
        return $this->render('courses/courses.html.twig', [
            'listcours' => $a,
        ]);
    }

    #[Route('/addcours', name: 'app_addcours')]
    public function addcours(Request $req,ManagerRegistry $m): Response
    {

        $em=$m->getManager();
        $cours=new cours();
        $form=$this->createForm(CoursesType::class,$cours);
        $form->handleRequest($req);


     if($form->isSubmitted() && $form->isValid()){
        $em->persist($cours);
        $em->flush();
        return $this->redirectToRoute('app_showcourses');
     }
         return $this->render('courses/addcourses.html.twig', [
            'f' => $form,
        ]);
    }

    #[Route('/deletecours/{id}', name: 'app_deletecours')]
    public function deletecours($id,ManagerRegistry $m,CoursRepository $coursrepo): Response
    {

        $em=$m->getManager();
        $del=$coursrepo->find($id);
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('app_showcourses');
    }

    #[Route('/updatecours/{id}', name: 'app_updatecours')]
public function updatecours($id, Request $req, ManagerRegistry $m, CoursRepository $coursrepo): Response
{
    $em = $m->getManager();
    $cours = $coursrepo->find($id);
    $form = $this->createForm(CoursesType::class, $cours);
    $form->handleRequest($req);

    if($form->isSubmitted() && $form->isValid()) {
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
        $a=$coursrepo->findAll();
        //$a=$authorrepo->findbyusername();
       // $a=$authorrepo->trieDescUsername();
        //$a=$coursrepo->trieAcsUsername();
        
        return $this->render('courses/coursesback.html.twig', [
            'listcours' => $a,
        ]);
    }

#[Route('/addcoursback', name: 'app_addcoursback')]
    public function addcoursback(Request $req,ManagerRegistry $m): Response
    {

        $em=$m->getManager();
        $cours=new cours();
        $form=$this->createForm(CoursesType::class,$cours);
        $form->handleRequest($req);


     if($form->isSubmitted() && $form->isValid()){
        $em->persist($cours);
        $em->flush();
        return $this->redirectToRoute('app_showcoursesback');
     }
         return $this->render('courses/addcoursesback.html.twig', [
            'f' => $form,
        ]);
    }

#[Route('/deletecoursback/{id}', name: 'app_deletecoursback')]
    public function deletecoursback($id,ManagerRegistry $m,CoursRepository $coursrepo): Response
    {

        $em=$m->getManager();
        $del=$coursrepo->find($id);
        $em->remove($del);
        $em->flush();
        return $this->redirectToRoute('app_showcourses');
    }

    #[Route('/updatecoursback/{id}', name: 'app_updatecoursback')]
public function updatecoursback($id, Request $req, ManagerRegistry $m, CoursRepository $coursrepo): Response
{
    $em = $m->getManager();
    $cours = $coursrepo->find($id);
    $form = $this->createForm(CoursesType::class, $cours);
    $form->handleRequest($req);

    if($form->isSubmitted() && $form->isValid()) {
        $em->flush();  // No need for persist on existing entity
        return $this->redirectToRoute('app_showcourses');
    }
    
    return $this->render('courses/updatecourses.html.twig', [
        'f' => $form,
    ]);
}

}


