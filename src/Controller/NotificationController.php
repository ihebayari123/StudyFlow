<?php
// src/Controller/NotificationController.php

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
    
    // Pour le render controller dans le template
    public function getUnreadForMenu(NotificationService $notificationService)
    {
        return $notificationService->getUnreadForAdmin();
    }

    public function getUnreadCount(NotificationService $notificationService): Response
    {
        $count = count($notificationService->getUnreadForAdmin());
        
        // ⚠️ Il faut retourner un objet Response, pas un nombre
        return new Response((string)$count);
    }

    #[Route('/notifications/mark-all-read', name: 'app_notifications_mark_all_read')]  // ← supprime methods
public function markAllAsRead(NotificationService $notificationService): RedirectResponse
{
    $notificationService->markAllAsRead();
    $this->addFlash('success', 'Toutes les notifications ont été marquées comme lues');
    return $this->redirectToRoute('app_admin_notifications');
}
}