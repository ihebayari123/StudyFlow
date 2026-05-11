<?php
namespace App\Controller;

use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class NotificationController extends AbstractController
{
    #[Route('/notifications/unread', name: 'app_notifications_unread')]
    public function getUnread(NotificationService $notificationService)
    {
        return $this->json($notificationService->getUnreadForAdmin());
    }

    #[Route('/notifications/menu', name: 'app_notifications_menu')]
    public function getUnreadForMenu(NotificationService $notificationService): Response
    {
        return $this->render('admin/Partials/_notifications_dropdown.html.twig', [
            'notifications' => $notificationService->getUnreadForAdmin()
        ]);
    }

    #[Route('/notifications/count', name: 'app_notifications_count')]
    public function getUnreadCount(NotificationService $notificationService): Response
    {
        $count = count($notificationService->getUnreadForAdmin());
        return $this->render('admin/Partials/_notifications_count.html.twig', [
            'count' => $count
        ]);
    }

    #[Route('/notifications/mark-all-read', name: 'app_notifications_mark_all_read')]
    public function markAllAsRead(NotificationService $notificationService): RedirectResponse
    {
        $notificationService->markAllAsRead();
        $this->addFlash('success', 'Toutes les notifications ont été marquées comme lues');
        return $this->redirectToRoute('app_admin_notifications');
    }
}