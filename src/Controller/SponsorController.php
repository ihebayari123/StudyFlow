<?php

namespace App\Controller;

use App\Entity\Sponsor;
use App\Form\SponsorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sponsor')]
class SponsorController extends AbstractController
{
    // ========================
    // HOME REDIRECT
    // ========================
    #[Route('/', name: 'app_sponsor')]
    public function home(): Response
    {
        return $this->redirectToRoute('sponsor_index');
    }

    // ========================
    // READ (LIST) + SEARCH
    // ========================
    #[Route('/list', name: 'sponsor_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        // Mot clé recherche
        $search = $request->query->get('search');

        // QueryBuilder
        $qb = $em->getRepository(Sponsor::class)->createQueryBuilder('s')
                ->leftJoin('s.eventTitre', 'e') // JOIN avec Event
                ->addSelect('e');

        // Recherche
        if ($search) {
            $qb->andWhere('
                s.nomSponsor LIKE :search
                OR s.type LIKE :search
                OR e.titre LIKE :search
            ')
            ->setParameter('search', '%' . $search . '%');
        }

        // Tri (optionnel)
        $qb->orderBy('s.nomSponsor', 'ASC');

        $sponsors = $qb->getQuery()->getResult();

        return $this->render('sponsor/index.html.twig', [
            'sponsors' => $sponsors,
            'search'   => $search,
        ]);
    }

    // ========================
    // CREATE
    // ========================
    #[Route('/create', name: 'sponsor_create', methods: ['GET','POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $sponsor = new Sponsor();

        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($sponsor);
            $em->flush();

            $this->addFlash('success', 'Sponsor ajouté avec succès');

            return $this->redirectToRoute('sponsor_index');
        }

        return $this->render('sponsor/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ========================
    // UPDATE
    // ========================
    #[Route('/{id}/edit', name: 'sponsor_edit', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $sponsor = $em->getRepository(Sponsor::class)->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException('Sponsor non trouvé');
        }

        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Sponsor modifié avec succès');

            return $this->redirectToRoute('sponsor_index');
        }

        return $this->render('sponsor/edit.html.twig', [
            'form'    => $form->createView(),
            'sponsor' => $sponsor,
        ]);
    }

    // ========================
    // DELETE
    // ========================
    #[Route('/{id}/delete', name: 'sponsor_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $sponsor = $em->getRepository(Sponsor::class)->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException('Sponsor non trouvé');
        }

        if ($this->isCsrfTokenValid('delete'.$sponsor->getId(), $request->request->get('_token'))) {

            $em->remove($sponsor);
            $em->flush();

            $this->addFlash('success', 'Sponsor supprimé avec succès');
        }
        else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('sponsor_index');
    }
}
