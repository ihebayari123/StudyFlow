<?php

    namespace App\Service;

    use App\Entity\Notification;
    use App\Entity\Utilisateur;
    use Doctrine\ORM\EntityManagerInterface;

    class NotificationService
    {
        public function __construct(
            private EntityManagerInterface $em
        ) {}

        public function notifyUserBlocked(Utilisateur $user): void
        {
            $notification = new Notification();
            $notification->setTitle('🔒 Utilisateur bloqué');
            $notification->setMessage(sprintf(
                'L\'utilisateur %s %s (%s) a été automatiquement bloqué pour activité suspecte (risque > 50).',
                $user->getPrenom(),
                $user->getNom(),
                $user->getEmail()
            ));
            $notification->setType('danger');
            $notification->setUser(null); // notification pour tous les admins

            $this->em->persist($notification);
            //$this->em->flush();
        }

        public function getUnreadForAdmin(): array
        {
            return $this->em->getRepository(Notification::class)
                ->createQueryBuilder('n')
                ->where('n.isRead = false')
                ->orderBy('n.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
        }

        public function markAsRead(Notification $notification): void
        {
            $notification->setIsRead(true);
            $this->em->flush();
        }

        public function markAllAsRead(): void
{
    $this->em->createQueryBuilder()
        ->update(Notification::class, 'n')
        ->set('n.isRead', true)
        ->where('n.isRead = false')
        ->getQuery()
        ->execute();
}
    }