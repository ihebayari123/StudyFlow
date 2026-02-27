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
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

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

    // ================= CREATE =================
    // ⚠️ DOIT être AVANT /{id}/edit et /{id}/delete

    #[Route('/events/create', name: 'event_create')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
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

    // ================= GENERATE DESCRIPTION (IA) =================
    // ⚠️ DOIT être AVANT /{id}/edit et /{id}/delete

    #[Route('/events/generate-description', name: 'event_generate_description', methods: ['POST'])]
    public function generateDescription(Request $request): Response
    {
        $titre = trim($request->request->get('titre', ''));
        $type  = trim($request->request->get('type', ''));
        $lieu  = trim($request->request->get('lieu', ''));
        $date  = trim($request->request->get('date', ''));

        if (empty($titre) || empty($type)) {
            return $this->json(['description' => 'Titre et type sont requis.'], 400);
        }

        $contextMap = [
            'conférence' => 'une conférence académique réunissant étudiants et experts du domaine',
            'hackathon'  => 'un hackathon intensif où les étudiants développent des solutions innovantes en équipe',
            'formation'  => 'une formation pratique destinée à renforcer les compétences techniques des étudiants',
            'bootcamp'   => 'un bootcamp intensif de montée en compétences pour les étudiants',
            'workshop'   => 'un atelier interactif favorisant l\'apprentissage par la pratique',
            'seminaire'  => 'un séminaire enrichissant autour d\'une thématique clé pour les étudiants',
            'networking' => 'un événement de networking pour connecter étudiants, professionnels et recruteurs',
        ];

        $typeKey  = mb_strtolower($type);
        $contexte = $contextMap[$typeKey] ?? "un événement académique dédié aux étudiants";

        $lieuInfo = $lieu ? "Lieu : $lieu." : '';
        $dateInfo = $date ? "Date : $date." : '';

        $prompt = <<<PROMPT
Tu es un rédacteur professionnel spécialisé dans la communication événementielle universitaire.
Rédige une description courte, dynamique et engageante en français pour l'événement suivant :

- Titre : $titre
- Type : $type ($contexte)
$lieuInfo
$dateInfo

Consignes strictes :
- Exactement 3 phrases
- Ton professionnel mais motivant, ciblé étudiants
- Mets en avant l'opportunité, l'apprentissage et la valeur ajoutée
- Pas de bullet points, pas de titres, texte continu uniquement
- Ne répète pas le titre mot pour mot

Description :
PROMPT;

        try {
            $payload = json_encode([
                'model'  => 'gemma:2b',
                'prompt' => $prompt,
                'stream' => false,
                'options' => [
                    'temperature'    => 0.7,
                    'top_p'          => 0.9,
                    'num_predict'    => 200,
                    'repeat_penalty' => 1.1,
                ],
            ]);

            $ch = curl_init('http://127.0.0.1:11434/api/generate');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 120,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return $this->json(['description' => 'Connexion Ollama échouée : ' . $curlError], 500);
            }

            if ($httpCode !== 200) {
                return $this->json(['description' => "Erreur Ollama (HTTP $httpCode)."], 500);
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['response'])) {
                return $this->json(['description' => 'Réponse invalide du modèle.'], 500);
            }

            $description = $this->cleanDescription($data['response']);

            return $this->json(['description' => $description]);

        } catch (\Exception $e) {
            return $this->json(['description' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    // ================= FRONT OFFICE =================
    // ⚠️ DOIT être AVANT /{id}/edit et /{id}/delete

    #[Route('/events/front', name: 'events_front')]
    public function frontIndex(EntityManagerInterface $em): Response
    {
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

    // ================= CALENDRIER =================
    // ⚠️ DOIT être AVANT /{id}/edit et /{id}/delete

    #[Route('/events/front/calendrier', name: 'events_calendar')]
    public function calendar(Request $request, EntityManagerInterface $em): Response
    {
        $month = $request->query->get('month') ?? date('m');
        $year  = $request->query->get('year')  ?? date('Y');

        $start = new \DateTime("$year-$month-01 00:00:00");
        $end   = clone $start;
        $end->modify('last day of this month')->setTime(23, 59, 59);

        $events = $em->createQuery(
            'SELECT e FROM App\Entity\Event e
             WHERE e.dateCreation BETWEEN :start AND :end
             ORDER BY e.dateCreation ASC'
        )
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getResult();

        return $this->render('events/calendar_front.html.twig', [
            'events' => $events,
            'month'  => $month,
            'year'   => $year,
        ]);
    }

    // ================= EDIT =================
    // ⚠️ Routes avec {id} EN DERNIER

    #[Route('/events/{id}/edit', name: 'event_edit')]
    public function edit(
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->handleImageUpload($form, $event, $slugger);

            $em->flush();

            $this->addFlash('success', 'Événement mis à jour');

            return $this->redirectToRoute('app_events');
        }

        return $this->render('events/edit.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }

    // ================= DELETE =================

    #[Route('/events/{id}/delete', name: 'event_delete', methods: ['POST'])]
    public function delete(
        Event $event,
        Request $request,
        EntityManagerInterface $em
    ): RedirectResponse {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $token)) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé');
        }

        return $this->redirectToRoute('app_events');
    }

    // ================= HELPERS PRIVÉS =================

    private function handleImageUpload($form, Event $event, SluggerInterface $slugger): void
    {
        $imageFile = $form->get('image')->getData();

        if ($imageFile instanceof UploadedFile) {

            $originalFilename = pathinfo(
                $imageFile->getClientOriginalName(),
                PATHINFO_FILENAME
            );

            $safeFilename = $slugger->slug($originalFilename);
            $newFilename  = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

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

    private function cleanDescription(string $raw): string
    {
        if (str_contains($raw, 'Description :')) {
            $raw = substr($raw, strpos($raw, 'Description :') + strlen('Description :'));
        }

        $clean = strip_tags($raw);
        $clean = preg_replace('/[\*\#]+/', '', $clean);
        $clean = preg_replace('/^\s*[-–]\s*/m', '', $clean);

        preg_match_all('/[^.!?]+[.!?]+/', $clean, $matches);
        $sentences = array_slice(array_filter(array_map('trim', $matches[0])), 0, 3);

        return implode(' ', $sentences);
    }
}
