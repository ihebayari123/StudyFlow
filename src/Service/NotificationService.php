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

        public function notifyHighRiskUser(Utilisateur $user, int $riskScore): void
{
    error_log("📝 DÉBUT notifyHighRiskUser pour " . $user->getEmail() . " (score: $riskScore)");
    
    $details = [];
    
    if ($user->getFailedLoginAttempts() > 3) {
        $details[] = "{$user->getFailedLoginAttempts()} tentatives échouées";
    }
    
    if ($user->getLastLogin() && (new \DateTime())->diff($user->getLastLogin())->days > 30) {
        $details[] = "Dernière connexion il y a " . (new \DateTime())->diff($user->getLastLogin())->days . " jours";
    }
    
    if ($user->getLoginFrequency() < 5) {
        $details[] = "Faible fréquence de connexion";
    }
    
    $detailsText = $details ? " Raisons : " . implode(", ", $details) : "";
    
    $notification = new Notification();
    $notification->setTitle('⚠️ Utilisateur à risque élevé');
    $notification->setMessage(sprintf(
        'L\'utilisateur %s %s (%s) présente un risque de %d/100.%s',
        $user->getPrenom(),
        $user->getNom(),
        $user->getEmail(),
        $riskScore,
        $detailsText
    ));
    $notification->setType('warning');
    $notification->setUser(null);

    $this->em->persist($notification);
    $this->em->flush();
    
    error_log("✅ FIN notifyHighRiskUser - Notification persistée en base");
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