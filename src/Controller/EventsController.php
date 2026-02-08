<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Utilisateur;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class EventsController extends AbstractController
{
    // ================= ADMIN LIST =================

    #[Route('/events', name: 'app_events')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        $qb = $em->getRepository(Event::class)->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.titre LIKE :search OR e.type LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($sort === 'asc' || $sort === 'desc') {
            $qb->orderBy('e.dateCreation', $sort);
        } else {
            $qb->orderBy('e.dateCreation', 'desc');
        }

        $events = $qb->getQuery()->getResult();

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    // ================= FRONT OFFICE =================

  #[Route('/events/front', name: 'events_front')]
public function frontIndex(EntityManagerInterface $em): Response
{
    // On récupère tous les événements avec leurs sponsors
    $events = $em->createQuery(
        'SELECT e, s
         FROM App\Entity\Event e
         LEFT JOIN e.sponsors s
         ORDER BY e.dateCreation DESC'
    )->getResult();

    return $this->render('events/index_front.html.twig', [
        'events' => $events,
    ]);
}


    // ================= CREATE =================

    #[Route('/events/create', name: 'event_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response
    {
        $event = new Event();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $em->getRepository(Utilisateur::class)->find(1);

            if (!$user) {
                throw $this->createNotFoundException('Utilisateur ID 1 introuvable');
            }

            $event->setUser($user);

            $this->handleImageUpload($form, $event, $slugger);

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès');

            return $this->redirectToRoute('app_events');
        }

        return $this->render('events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ================= EDIT =================

    #[Route('/events/{id}/edit', name: 'event_edit')]
    public function edit(
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response
    {
        $form = $this->createForm(EventType::class, $event);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->handleImageUpload($form, $event, $slugger);

            $em->flush();

            $this->addFlash('success', 'Événement mis à jour');

            return $this->redirectToRoute('app_events');
        }

        return $this->render('events/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    // ================= DELETE =================

    #[Route('/events/{id}/delete', name: 'event_delete', methods: ['POST'])]
    public function delete(
        Event $event,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse
    {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $token)) {

            $em->remove($event);
            $em->flush();

            $this->addFlash('success', 'Événement supprimé');
        }

        return $this->redirectToRoute('app_events');
    }

    // ================= IMAGE UPLOAD =================

    private function handleImageUpload($form, Event $event, SluggerInterface $slugger): void
    {
        $imageFile = $form->get('image')->getData();

        if ($imageFile instanceof UploadedFile) {

            $originalFilename = pathinfo(
                $imageFile->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $safeFilename = $slugger->slug($originalFilename);

            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {

                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );

                $event->setImage($newFilename);

            } catch (\Exception $e) {

                $event->setImage('default.png');
            }

        } elseif (!$event->getImage()) {

            $event->setImage('default.png');
        }
    }
}
