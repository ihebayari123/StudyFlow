<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Entity\Cours;
use App\Service\OllamaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AiCourseController extends AbstractController
{
    public function __construct(
        private OllamaService $ollamaService,
        private EntityManagerInterface $em,
    ) {}

    #[Route('/admin/ai-course', name: 'app_ai_course', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('courses/ai_generate.html.twig');
    }

    #[Route('/admin/ai-course/generate', name: 'app_ai_course_generate', methods: ['POST'])]
    public function generate(Request $request): Response
    {
        try {
            $data         = json_decode($request->getContent(), true);
            $courseName   = trim($data['courseName'] ?? '');
            $chapterCount = max(1, min(10, (int)($data['chapterCount'] ?? 5)));

            if (empty($courseName)) {
                return $this->json(['error' => 'Course name is required.'], 400);
            }

            $generated = $this->ollamaService->generateCourse($courseName, $chapterCount);
            return $this->json(['success' => true, 'course' => $generated]);

        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/admin/ai-course/save', name: 'app_ai_course_save', methods: ['POST'])]
    public function save(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (empty($data['titre']) || empty($data['description']) || empty($data['chapitres'])) {
                return $this->json(['error' => 'Incomplete data.'], 400);
            }

            $user = $this->getUser(); // ✅ built-in — no import needed
            if (!$user) {
                return $this->json(['error' => 'You must be logged in.'], 401);
            }

            $cours = new Cours();
            $cours->setTitre(mb_substr($data['titre'], 0, 255));
            $cours->setDescription(mb_substr($data['description'], 0, 255));
            $imageKeyword = urlencode(strtolower(trim($data['titre'])));
            $cours->setImage('https://source.unsplash.com/800x450/?' . $imageKeyword);
            $cours->setUser($user);
            $this->em->persist($cours);

            foreach ($data['chapitres'] as $index => $chData) {
    $chapitre = new Chapitre();
    $chapitre->setTitre(mb_substr($chData['titre'] ?? 'Chapter ' . ($index + 1), 0, 255));
    $chapitre->setContenu($chData['contenu'] ?? '');
    $chapitre->setOrdre((int)($chData['ordre'] ?? $index + 1));
    $chapitre->setDurationMinutes((int)($chData['durationMinutes'] ?? 30));
    $chapitre->setContentType($chData['contentType'] ?? 'text');

    // Video URL
    if (!empty($chData['videoUrl'])) {
        $chapitre->setVideoUrl(mb_substr($chData['videoUrl'], 0, 500));
    }

    // Resource links
    if (!empty($chData['links']) && is_array($chData['links'])) {
        $chapitre->setLinks($chData['links']);
    }

    $chapitre->setCourse($cours);
    $this->em->persist($chapitre);
}

            $this->em->flush();

            return $this->json([
                'success'  => true,
                'courseId' => $cours->getId(),
                'redirect' => $this->generateUrl('app_chapitres', ['courseId' => $cours->getId()]),
            ]);

        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}