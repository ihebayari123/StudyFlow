<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\NotificationService; 
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Notification; 

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/base_admin.html.twig');
    }

    #[Route('/admin/notifications', name: 'app_admin_notifications')]
public function notifications(NotificationService $notificationService, EntityManagerInterface $em): Response
{
    $unread = $notificationService->getUnreadForAdmin();
    
    // Compter toutes les notifications lues
    $readCount = $em->getRepository(Notification::class)
        ->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.isRead = true')
        ->getQuery()
        ->getSingleScalarResult();
    
    return $this->render('admin/notifications.html.twig', [
        'notifications' => $unread,
        'notifications_read' => $readCount
    ]);
}

    #[Route('/admin/notification/read/{id}', name: 'app_notification_read')]
    public function markAsRead($id, EntityManagerInterface $em, NotificationService $notificationService): Response
    {
        $notification = $em->getRepository(Notification::class)->find($id);
        
        if ($notification) {
            $notificationService->markAsRead($notification);
            $this->addFlash('success', 'Notification marquée comme lue');
        } else {
            $this->addFlash('error', 'Notification non trouvée');
        }
        
        return $this->redirectToRoute('app_admin_notifications');
    }

    
}
