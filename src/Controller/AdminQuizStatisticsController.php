<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\QuizAttempt;
use App\Repository\UtilisateurRepository;
use App\Repository\QuizAttemptRepository;
use App\Repository\QuizRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AdminQuizStatisticsController extends AbstractController
{
    #[Route('/admin/quiz/statistics', name: 'app_admin_quiz_statistics')]
    public function index(QuizRepository $quizRepo): Response
    {
        $quizzes = $quizRepo->findAll();
        $globalStats = [];
        
        foreach ($quizzes as $quiz) {
            $stats = $this->calculateQuizStats($quiz);
            $globalStats[$quiz->getId()] = $stats;
        }
        
        return $this->render('admin_quiz_statistics/index.html.twig', [
            'quizzes' => $quizzes,
            'globalStats' => $globalStats,
        ]);
    }

    #[Route('/admin/quiz/statistics/all', name: 'admin_quiz_statistics_list')]
    public function statisticsList(
        QuizRepository $quizRepo
    ): Response {
        
        $quizzes = $quizRepo->findAll();
        $allStats = [];
        
        foreach ($quizzes as $quiz) {
            $stats = $this->calculateQuizStats($quiz);
            $timeStats = $this->calculateTimeStats($quiz);
            $userStats = $this->calculateUserStats($quiz);
            $scoreDistribution = $this->calculateScoreDistribution($quiz);
            
            $allStats[$quiz->getId()] = [
                'quiz' => $quiz,
                'stats' => $stats,
                'timeStats' => $timeStats,
                'userStats' => $userStats,
                'scoreDistribution' => $scoreDistribution,
            ];
        }
        
        return $this->render('admin_quiz/quiz_statistics.html.twig', [
            'allStats' => $allStats,
        ]);
    }
    
    private function calculateQuizStats(Quiz $quiz): array
    {
        $attempts = $quiz->getQuizAttempts();
        $totalAttempts = count($attempts);
        
        if ($totalAttempts === 0) {
            return [
                'total_attempts' => 0,
                'completed_attempts' => 0,
                'in_progress_attempts' => 0,
                'total_points' => 0,
                'average_points' => 0,
                'average_percentage' => 0,
                'best_score' => 0,
                'worst_score' => 0,
                'median_score' => 0,
                'total_questions_correct' => 0,
                'average_questions_correct' => 0,
                'success_rate' => 0,
                'passing_rate' => 0,
                'standard_deviation' => 0,
            ];
        }
        
        $totalPoints = 0;
        $totalQuestionsCorrect = 0;
        $scores = [];
        $completedAttempts = 0;
        $inProgressAttempts = 0;
        $bestScore = 0;
        $worstScore = PHP_INT_MAX;
        
        foreach ($attempts as $attempt) {
            $points = $attempt->getScorePoints();
            
            $totalPoints += $points;
            $totalQuestionsCorrect += $attempt->getScoreQuestions();
            $scores[] = $points;
            
            if ($points > $bestScore) {
                $bestScore = $points;
            }
            
            if ($points < $worstScore) {
                $worstScore = $points;
            }
            
            // Déterminer le statut basé sur finishedAt
            if ($attempt->getFinishedAt() !== null) {
                $completedAttempts++;
            } else {
                $inProgressAttempts++;
            }
        }
        
        $worstScore = $worstScore === PHP_INT_MAX ? 0 : $worstScore;
        
        $averagePoints = round($totalPoints / $totalAttempts, 2);
        $averageQuestionsCorrect = round($totalQuestionsCorrect / $totalAttempts, 2);
        
        // Calculer le score maximum possible (10 points par question par défaut)
        $maxPossibleScore = $quiz->getQuestions()->count() * 10;
        $averagePercentage = $maxPossibleScore > 0 
            ? round(($averagePoints / $maxPossibleScore) * 100, 2) 
            : 0;
        
        // Calculer la médiane
        sort($scores);
        $middle = floor(($totalAttempts - 1) / 2);
        $medianScore = $totalAttempts % 2 
            ? $scores[$middle] 
            : ($scores[$middle] + $scores[$middle + 1]) / 2;
        
        // Calculer l'écart type
        $variance = 0;
        foreach ($scores as $score) {
            $variance += pow($score - $averagePoints, 2);
        }
        $variance /= $totalAttempts;
        $standardDeviation = round(sqrt($variance), 2);
        
        // Taux de réussite (score ≥ 50% du maximum)
        $passingScore = $maxPossibleScore * 0.5;
        $passingAttempts = count(array_filter($scores, function($score) use ($passingScore) {
            return $score >= $passingScore;
        }));
        $passingRate = round(($passingAttempts / $totalAttempts) * 100, 2);
        
        // Taux de bonnes réponses
        $totalPossibleQuestions = $totalAttempts * $quiz->getQuestions()->count();
        $successRate = $totalPossibleQuestions > 0
            ? round(($totalQuestionsCorrect / $totalPossibleQuestions) * 100, 2)
            : 0;
        
        return [
            'total_attempts' => $totalAttempts,
            'completed_attempts' => $completedAttempts,
            'in_progress_attempts' => $inProgressAttempts,
            'total_points' => $totalPoints,
            'average_points' => $averagePoints,
            'average_percentage' => $averagePercentage,
            'best_score' => $bestScore,
            'worst_score' => $worstScore,
            'median_score' => round($medianScore, 2),
            'total_questions_correct' => $totalQuestionsCorrect,
            'average_questions_correct' => $averageQuestionsCorrect,
            'success_rate' => $successRate,
            'passing_rate' => $passingRate,
            'standard_deviation' => $standardDeviation,
            'max_possible_score' => $maxPossibleScore,
        ];
    }
    
    private function calculateTimeStats(Quiz $quiz): array
    {
        $attempts = $quiz->getQuizAttempts();
        $completionTimes = [];
        $dailyAttempts = [];
        $hourlyDistribution = array_fill(0, 24, 0);
        
        foreach ($attempts as $attempt) {
            // Calculer le temps de complétion si disponible
            if ($attempt->getFinishedAt() !== null && $attempt->getStartedAt() !== null) {
                $timeDiff = $attempt->getFinishedAt()->getTimestamp() - $attempt->getStartedAt()->getTimestamp();
                $completionTimes[] = $timeDiff;
            }
            
            // Distribution quotidienne
            if ($attempt->getStartedAt() !== null) {
                $date = $attempt->getStartedAt()->format('Y-m-d');
                if (!isset($dailyAttempts[$date])) {
                    $dailyAttempts[$date] = 0;
                }
                $dailyAttempts[$date]++;
                
                // Distribution horaire
                $hour = (int)$attempt->getStartedAt()->format('H');
                $hourlyDistribution[$hour]++;
            }
        }
        
        $averageCompletionTime = count($completionTimes) > 0
            ? round(array_sum($completionTimes) / count($completionTimes))
            : 0;
        
        return [
            'average_completion_time' => $averageCompletionTime,
            'average_completion_time_formatted' => $this->formatTime($averageCompletionTime),
            'fastest_completion_time' => count($completionTimes) > 0 ? min($completionTimes) : 0,
            'slowest_completion_time' => count($completionTimes) > 0 ? max($completionTimes) : 0,
            'daily_attempts' => $dailyAttempts,
            'hourly_distribution' => $hourlyDistribution,
            'peak_hour' => count($hourlyDistribution) > 0 ? array_search(max($hourlyDistribution), $hourlyDistribution) : 0,
        ];
    }
    
    private function calculateUserStats(Quiz $quiz): array
    {
        $attempts = $quiz->getQuizAttempts();
        $usersStats = [];
        $uniqueUsers = [];
        
        foreach ($attempts as $attempt) {
            $user = $attempt->getUser();
            if ($user) {
                $userId = $user->getId();
                if (!isset($uniqueUsers[$userId])) {
                    $uniqueUsers[$userId] = [
                        'user' => $user,
                        'attempts_count' => 0,
                        'total_score' => 0,
                        'best_score' => 0,
                        'last_attempt' => null,
                    ];
                }
                
                $uniqueUsers[$userId]['attempts_count']++;
                $score = $attempt->getScorePoints();
                $uniqueUsers[$userId]['total_score'] += $score;
                
                if ($score > $uniqueUsers[$userId]['best_score']) {
                    $uniqueUsers[$userId]['best_score'] = $score;
                }
                
                if ($uniqueUsers[$userId]['last_attempt'] === null || 
                    ($attempt->getStartedAt() !== null && $attempt->getStartedAt() > $uniqueUsers[$userId]['last_attempt'])) {
                    $uniqueUsers[$userId]['last_attempt'] = $attempt->getStartedAt();
                }
            }
        }
        
        foreach ($uniqueUsers as &$userStat) {
            $userStat['average_score'] = $userStat['attempts_count'] > 0
                ? round($userStat['total_score'] / $userStat['attempts_count'], 2)
                : 0;
        }
        
        return [
            'total_unique_users' => count($uniqueUsers),
            'users_stats' => $uniqueUsers,
            'average_attempts_per_user' => count($uniqueUsers) > 0
                ? round(count($attempts) / count($uniqueUsers), 2)
                : 0,
        ];
    }
    
    private function calculateScoreDistribution(Quiz $quiz): array
    {
        $attempts = $quiz->getQuizAttempts();
        $maxPossibleScore = $quiz->getQuestions()->count() * 10;
        
        $distribution = [
            '0-20%' => 0,
            '21-40%' => 0,
            '41-60%' => 0,
            '61-80%' => 0,
            '81-100%' => 0,
        ];
        
        foreach ($attempts as $attempt) {
            $percentage = $maxPossibleScore > 0
                ? ($attempt->getScorePoints() / $maxPossibleScore) * 100
                : 0;
            
            if ($percentage <= 20) {
                $distribution['0-20%']++;
            } elseif ($percentage <= 40) {
                $distribution['21-40%']++;
            } elseif ($percentage <= 60) {
                $distribution['41-60%']++;
            } elseif ($percentage <= 80) {
                $distribution['61-80%']++;
            } else {
                $distribution['81-100%']++;
            }
        }
        
        return $distribution;
    }
    
    private function formatTime($seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' secondes';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            return $minutes . ' min ' . ($remainingSeconds ? $remainingSeconds . ' s' : '');
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . ' h ' . ($minutes ? $minutes . ' min' : '');
        }
    }
}