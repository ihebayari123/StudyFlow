<?php
namespace App\Controller;

use App\Entity\QuestionChoix;
use App\Entity\QuestionVraiFaux;
use App\Entity\QuestionTexteLibre;
use App\Repository\QuizRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class QuizAIController extends AbstractController
{
    // ETAPE 1: génération + affichage sans sauvegarde
    #[Route('/admin/quiz/ai-generate', name: 'quiz_ai_generate', methods: ['GET', 'POST'])]
    public function generate(
        Request $request,
        QuizRepository $quizRepo,
        RequestStack $requestStack
    ): Response {
        $questions = [];
        $error = null;

        if ($request->isMethod('POST')) {
            $file = $request->files->get('pdf');
            $quizId = $request->request->get('quiz_id');

            if (!$file || $file->getClientOriginalExtension() !== 'pdf') {
                $error = "Veuillez uploader un fichier PDF valide.";
            } else {
                $pdfPath = sys_get_temp_dir() . '/' . uniqid() . '.pdf';
                $file->move(sys_get_temp_dir(), basename($pdfPath));

                $scriptPath = $this->getParameter('kernel.project_dir') . '/ai/generate.py';
                $outputPath = sys_get_temp_dir() . '/questions_' . uniqid() . '.json';

                $command = sprintf(
                    'python %s %s %s %s 2>&1',
                    escapeshellarg($scriptPath),
                    escapeshellarg($pdfPath),
                    escapeshellarg($outputPath),
                    escapeshellarg($quizId)
                );

                exec($command, $output, $returnCode);

                if ($returnCode !== 0 || !file_exists($outputPath)) {
                    $error = "Erreur: " . implode("\n", $output);
                } else {
                    $data = json_decode(file_get_contents($outputPath), true);
                    $questions = $data['questions'] ?? [];

                    
                    $session = $requestStack->getSession();
                    $session->set('pending_questions', $questions);
                    $session->set('pending_quiz_id', $quizId);

                    unlink($pdfPath);
                    unlink($outputPath);
                }
            }
        }

        return $this->render('quiz_ai/generate.html.twig', [
            'questions' => $questions,
            'quizzes'   => $quizRepo->findAll(),
            'error'     => $error,
        ]);
    }

    
    #[Route('/quiz/ai-confirm', name: 'quiz_ai_confirm', methods: ['POST'])]
    public function confirm(
        Request $request,
        EntityManagerInterface $em,
        QuizRepository $quizRepo,
        RequestStack $requestStack
    ): Response {
        $session = $requestStack->getSession();
        $allQuestions = $session->get('pending_questions', []);
        $quizId = $session->get('pending_quiz_id');

        
        $approvedIndexes = $request->request->all('approved') ?? [];

        $quiz = $quizRepo->find($quizId);
        $imported = 0;

        foreach ($approvedIndexes as $index) {
            $q = $allQuestions[$index] ?? null;
            if (!$q) continue;

            $type = $q['type'] ?? '';

            if ($type === 'choix_multiple') {
                $question = new QuestionChoix();
                $question->setChoixA($q['choix_a'] ?? null);
                $question->setChoixB($q['choix_b'] ?? null);
                $question->setChoixC($q['choix_c'] ?? null);
                $question->setChoixD($q['choix_d'] ?? null);
                $question->setBonneReponseChoix($q['bonne_reponse_choix'] ?? null);
            } elseif ($type === 'vrai_faux') {
                $question = new QuestionVraiFaux();
                $question->setBonneReponseBool($q['bonne_reponse_bool'] ?? null);
            } else {
                $question = new QuestionTexteLibre();
                $question->setReponseAttendue($q['reponse_attendue'] ?? null);
            }

            $question->setTexte($q['texte']);
            $question->setNiveau($q['niveau']);
            $question->setIndice($q['indice'] ?? null);
            $question->setQuiz($quiz);

            $em->persist($question);
            $imported++;
        }

        $em->flush();
        $session->remove('pending_questions');
        $session->remove('pending_quiz_id');

        $this->addFlash('success', "✅ $imported question(s) ajoutée(s) avec succès!");

        return $this->redirectToRoute('quiz_ai_generate');
    }
}