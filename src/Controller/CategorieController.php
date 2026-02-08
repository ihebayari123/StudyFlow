<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


use Doctrine\Persistence\ManagerRegistry;
use App\Repository\TypeCategorieRepository;
use App\Entity\TypeCategorie;
use App\Form\CategorieType;
use Symfony\Component\HttpFoundation\Request;

final class CategorieController extends AbstractController
{
    #[Route('/categorie', name: 'app_categorie')]
    public function index(): Response
    {
        return $this->render('categorie/index.html.twig', [
            'controller_name' => 'CategorieController',
        ]);
    }



     #[Route('/showcategorie', name: 'app_showcategorie')]
    public function showcategorie(Request $request, TypeCategorieRepository $categorieRepo): Response
    {
        $search = $request->query->get('search', '');
        $tri = $request->query->get('tri', '');
        
        if ($tri === 'nom') {
            // Utilise DQL pour le tri
            $categories = $categorieRepo->triByNomCategorie();
        } elseif ($search) {
            // Utilise DQL pour la recherche
            $categories = $categorieRepo->findByNomCategorie($search);
        } else {
            $categories = $categorieRepo->findAll();
        }

        return $this->render('categorie/showcategorie.html.twig', [
            'listcategorie' => $categories,
            'search' => $search,
        ]);
    }



    //fonction 
    #[Route('/addcategorie', name: 'app_addcategorie')]
    public function addcategorie(Request $req, ManagerRegistry $m, TypeCategorieRepository $categorierepo): Response
    {
        $em = $m->getManager();
        $categorie=new TypeCategorie();
        
        $form=$this->createForm(CategorieType::class,$categorie);
        $form->handleRequest($req);//recuperation mn navigateur ll objet

        if($form->isSubmitted() && $form->isValid() ){
            $em->persist($categorie);//lehne baad tabbath l bd bl persist
            $em->flush();
        }

        
        return $this->render('categorie/addcategorie.html.twig', [
            'ff' => $form,  //appel 5ater showauthors.html.twig mayfhemch php
        ]);
    }




    //delete
    #[Route('/deletecategorie/{id}', name: 'app_deletecategorie')]
    public function deletecategorie($id, ManagerRegistry $m, TypeCategorieRepository $categorierepo): Response
    {
        $em = $m->getManager();
        $del = $categorierepo->find($id);
        $em->remove($del);
        $em->flush();


        return $this->redirectToRoute('app_showcategorie');
    }



    //fonction update
    #[Route('/updatecategorie/{id}', name: 'app_updatecategorie')]
    public function updatecategorie($id,Request $req, ManagerRegistry $m, TypeCategorieRepository $categorierepo): Response
    {
        $em = $m->getManager();
        $categorie=$categorierepo->find($id);
        
        $form=$this->createForm(CategorieType::class,$categorie);
        $form->handleRequest($req);//recuperation mn navigateur ll objet

        if($form->isSubmitted() && $form->isValid() ){
            $em->persist($categorie);//lehne baad tabbath l bd bl persist
            $em->flush();
        }

        
        return $this->render('categorie/updatecategorie.html.twig', [
            'fff1' => $form,  //appel 5ater showauthors.html.twig mayfhemch php
        ]);
    }




    ///recherche








}
