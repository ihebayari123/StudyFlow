<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ProduitRepository;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Component\HttpFoundation\Request;

final class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(): Response
    {
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
        ]);
    }


    #[Route('/showproduit', name: 'app_showproduit')]
    public function showproduit(ProduitRepository $produitrepo): Response
    {


        $a=$produitrepo->findAll(); // tjiblk lkol
        //$a=$joueurrepo->findbyusername(); // te5tarlk wehed m3adi fl parameter     rit hetha lkol esmou DQL
        //$a=$joueurrepo->treebyusername(); //treee

        //$a=$joueurrepo->treebyusername1(); /// querybuilder   querybuilder



        return $this->render('produit/showproduit.html.twig', [
            'listproduit' => $a,  //appel 5ater showauthors.html.twig mayfhemch php
        ]);
    }



    //fonction 
    #[Route('/addproduit', name: 'app_addproduit')]
    public function addproduit(Request $req, ManagerRegistry $m, ProduitRepository $produitrepo): Response
    {
        $em = $m->getManager();
        $produit=new Produit();
        
        $form=$this->createForm(ProduitType::class,$produit);
        $form->handleRequest($req);//recuperation mn navigateur ll objet

        if($form->isSubmitted() && $form->isValid() ){
            $em->persist($produit);//lehne baad tabbath l bd bl persist
            $em->flush();
        }

        
        return $this->render('produit/addproduit.html.twig', [
            'ff' => $form,  //appel 5ater showauthors.html.twig mayfhemch php
        ]);
    }



    #[Route('/deleteproduit/{id}', name: 'app_deleteproduit')]
    public function deleteproduit($id, ManagerRegistry $m, ProduitRepository $produitrepo): Response
    {
        $em = $m->getManager();
        $del = $produitrepo->find($id);
        $em->remove($del);
        $em->flush();


        return $this->redirectToRoute('app_showproduit');
    }


    //fonction update
    #[Route('/updateproduit/{id}', name: 'app_updateproduit')]
    public function updateproduit($id,Request $req, ManagerRegistry $m, ProduitRepository $produitrepo): Response
    {
        $em = $m->getManager();
        $produit=$produitrepo->find($id);
        
        $form=$this->createForm(ProduitType::class,$produit);
        $form->handleRequest($req);//recuperation mn navigateur ll objet

        if($form->isSubmitted() && $form->isValid() ){
            $em->persist($produit);//lehne baad tabbath l bd bl persist
            $em->flush();
        }

        
        return $this->render('produit/updateproduit.html.twig', [
            'ff' => $form,  //appel 5ater showauthors.html.twig mayfhemch php
        ]);
    }

 #[Route('/showproduit1', name: 'app_showproduit1')]
    public function showproduit1(ProduitRepository $produitrepo): Response
    {


        $a=$produitrepo->findAll(); // tjiblk lkol
        //$a=$joueurrepo->findbyusername(); // te5tarlk wehed m3adi fl parameter     rit hetha lkol esmou DQL
        //$a=$joueurrepo->treebyusername(); //treee

        //$a=$joueurrepo->treebyusername1(); /// querybuilder   querybuilder



        return $this->render('home/showproduit1.html.twig', [
            'listproduit1' => $a,  //appel 5ater showauthors.html.twig mayfhemch php
        ]);
    }


    

#[Route('/produits', name: 'app_home_produits')]
public function showproduits(Request $request, ProduitRepository $produitRepo): Response
{
    $search = $request->query->get('search', '');
    
    // Utilise DQL maintenant
    $produits = $produitRepo->findByCategorieName($search);
    
    return $this->render('home/showproduit1.html.twig', [
        'listproduit1' => $produits,
        'search' => $search,
    ]);
}





}
