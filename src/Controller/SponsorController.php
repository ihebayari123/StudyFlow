<?php

namespace App\Controller;

use App\Entity\Sponsor;
use App\Form\SponsorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

#[Route('/sponsor')]
class SponsorController extends AbstractController
{
    // ========================
    // HOME REDIRECT
    // ========================
    #[Route('/', name: 'app_sponsor')]
    public function home(): Response
    {
        return $this->redirectToRoute('sponsor_index');
    }

    // ========================
    // READ (LIST) + SEARCH
    // ========================
    #[Route('/list', name: 'sponsor_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $search = $request->query->get('search');

        $qb = $em->getRepository(Sponsor::class)->createQueryBuilder('s')
            ->leftJoin('s.eventTitre', 'e')
            ->addSelect('e');

        if ($search) {
            $qb->andWhere('
                s.nomSponsor LIKE :search
                OR s.type LIKE :search
                OR e.titre LIKE :search
            ')
            ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('s.nomSponsor', 'ASC');
        $sponsors = $qb->getQuery()->getResult();

        return $this->render('sponsor/index.html.twig', [
            'sponsors' => $sponsors,
            'search'   => $search,
        ]);
    }

    // ========================
    // CREATE
    // ========================
    #[Route('/create', name: 'sponsor_create', methods: ['GET','POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response
    {
        $sponsor = new Sponsor();
        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($sponsor);
            $em->flush();

            $eventTitre = $sponsor->getEventTitre()
                ? $sponsor->getEventTitre()->getTitre()
                : 'Non défini';

            $nomSponsor  = $sponsor->getNomSponsor();
            $type        = $sponsor->getType();
            $montant     = $sponsor->getMontant();

            $badgeColor = match($type) {
                'Gold'   => '#f1c40f',
                'Silver' => '#95a5a6',
                'Bronze' => '#cd6133',
                default  => '#2c3e50',
            };

            $today = (new \DateTime())->format('d/m/Y');

            try {
                $email = (new Email())
                    ->from('rokitube8@gmail.com')
                    ->to('rokitube8@gmail.com')
                    ->subject("Confirmation de partenariat – $nomSponsor & StudyFlow")
                    ->html("
                        <div style='font-family: Georgia, serif; max-width: 650px; margin: auto; color: #2c3e50;'>
                            <div style='background-color: #2c3e50; padding: 35px 40px; text-align: center;'>
                                <h1 style='color: #ffffff; margin: 0; font-size: 26px; letter-spacing: 2px;'>StudyFlow</h1>
                                <p style='color: #bdc3c7; margin: 6px 0 0; font-size: 13px; letter-spacing: 1px;'>GESTION DES PARTENARIATS & SPONSORS</p>
                            </div>
                            <div style='padding: 40px; background-color: #ffffff; border: 1px solid #e0e0e0;'>
                                <p style='font-size: 14px; color: #7f8c8d; text-align: right; margin: 0 0 30px;'>Tunis, le $today</p>
                                <p style='font-size: 16px; margin-bottom: 25px;'>Madame, Monsieur,</p>
                                <p style='font-size: 15px; line-height: 1.8; margin-bottom: 20px;'>
                                    Nous avons l'honneur de vous informer que le partenariat de
                                    <strong>$nomSponsor</strong> avec la plateforme <strong>StudyFlow</strong>
                                    a été officiellement enregistré et confirmé.
                                </p>
                                <p style='font-size: 15px; line-height: 1.8; margin-bottom: 30px;'>
                                    Nous tenons à vous exprimer notre sincère gratitude pour votre confiance
                                    et votre précieux soutien à l'événement <strong>« $eventTitre »</strong>.
                                    Votre engagement constitue un apport considérable à la réussite de cet événement.
                                </p>
                                <div style='background-color: #f4f6f8; border-left: 4px solid #2c3e50; padding: 25px; border-radius: 4px; margin-bottom: 30px;'>
                                    <h3 style='margin: 0 0 18px; color: #2c3e50; font-size: 15px; text-transform: uppercase; letter-spacing: 1px;'>Récapitulatif du partenariat</h3>
                                    <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                                        <tr><td style='padding: 10px 0; border-bottom: 1px solid #dce0e4; color: #7f8c8d; width: 45%;'>Nom du sponsor</td><td style='padding: 10px 0; border-bottom: 1px solid #dce0e4; font-weight: bold;'>$nomSponsor</td></tr>
                                        <tr><td style='padding: 10px 0; border-bottom: 1px solid #dce0e4; color: #7f8c8d;'>Événement</td><td style='padding: 10px 0; border-bottom: 1px solid #dce0e4; font-weight: bold;'>$eventTitre</td></tr>
                                        <tr><td style='padding: 10px 0; border-bottom: 1px solid #dce0e4; color: #7f8c8d;'>Niveau de partenariat</td><td style='padding: 10px 0; border-bottom: 1px solid #dce0e4;'><span style='background-color: $badgeColor; color: white; padding: 3px 12px; border-radius: 20px; font-size: 13px; font-weight: bold;'>$type</span></td></tr>
                                        <tr><td style='padding: 10px 0; color: #7f8c8d;'>Contribution financière</td><td style='padding: 10px 0; font-weight: bold; color: #27ae60; font-size: 16px;'>$montant €</td></tr>
                                    </table>
                                </div>
                                <p style='font-size: 15px; line-height: 1.8; margin-bottom: 20px;'>Nous restons à votre entière disposition pour toute information complémentaire.</p>
                                <p style='font-size: 15px; line-height: 1.8; margin-bottom: 35px;'>Dans l'attente de collaborer avec vous, nous vous prions d'agréer, Madame, Monsieur, l'expression de nos salutations distinguées.</p>
                                <div style='border-top: 1px solid #e0e0e0; padding-top: 20px;'>
                                    <p style='margin: 0; font-weight: bold; font-size: 15px;'>L'équipe StudyFlow</p>
                                    <p style='margin: 4px 0; color: #7f8c8d; font-size: 13px;'>Service Partenariats & Sponsors</p>
                                    <p style='margin: 4px 0; color: #7f8c8d; font-size: 13px;'>rokitube8@gmail.com</p>
                                </div>
                            </div>
                            <div style='background-color: #f4f6f8; padding: 18px 40px; text-align: center; border: 1px solid #e0e0e0; border-top: none;'>
                                <p style='margin: 0; font-size: 11px; color: #95a5a6;'>Cet email est généré automatiquement par StudyFlow · Gestion des Partenariats<br>© $today StudyFlow – Tous droits réservés</p>
                            </div>
                        </div>
                    ");

                $mailer->send($email);
                $this->addFlash('success', "Sponsor ajouté avec succès. Email de confirmation envoyé !");

            } catch (\Exception $e) {
                $logger->error('EMAIL ERROR: ' . $e->getMessage());
                $this->addFlash('error', 'Sponsor ajouté mais email non envoyé : ' . $e->getMessage());
            }

            return $this->redirectToRoute('sponsor_index');
        }

        return $this->render('sponsor/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ========================
    // UPDATE
    // ========================
    #[Route('/{id}/edit', name: 'sponsor_edit', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $sponsor = $em->getRepository(Sponsor::class)->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException('Sponsor non trouvé');
        }

        $form = $this->createForm(SponsorType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Sponsor modifié avec succès');
            return $this->redirectToRoute('sponsor_index');
        }

        return $this->render('sponsor/edit.html.twig', [
            'form'    => $form->createView(),
            'sponsor' => $sponsor,
        ]);
    }

    // ========================
    // DELETE
    // ========================
    #[Route('/{id}/delete', name: 'sponsor_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $sponsor = $em->getRepository(Sponsor::class)->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException('Sponsor non trouvé');
        }

        if ($this->isCsrfTokenValid('delete'.$sponsor->getId(), $request->request->get('_token'))) {
            $em->remove($sponsor);
            $em->flush();
            $this->addFlash('success', 'Sponsor supprimé avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('sponsor_index');
    }

    // ========================
    // AFFICHER LE CONTRAT ← CETTE ROUTE ÉTAIT MANQUANTE !
    // ========================
    #[Route('/{id}/contract', name: 'sponsor_contract', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function contract(int $id, EntityManagerInterface $em): Response
    {
        $sponsor = $em->getRepository(Sponsor::class)->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException('Sponsor non trouvé');
        }

        return $this->render('sponsor/contract.html.twig', [
            'sponsor' => $sponsor,
            'today'   => new \DateTime(),
        ]);
    }

    // ========================
    // GÉNÉRER LE PDF DU CONTRAT (Chrome Headless)
    // ========================
    #[Route('/{id}/contract/pdf', name: 'sponsor_contract_pdf', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function contractPdf(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $sponsor = $em->getRepository(Sponsor::class)->find($id);

        if (!$sponsor) {
            throw $this->createNotFoundException('Sponsor non trouvé');
        }

        $html = $request->request->get('html_content');

        if (!$html) {
            return new Response('Contenu HTML manquant.', 400);
        }

        $tmpDir  = sys_get_temp_dir();
        $tmpHtml = $tmpDir . DIRECTORY_SEPARATOR . 'contrat_' . $id . '_' . time() . '.html';
        $tmpPdf  = $tmpDir . DIRECTORY_SEPARATOR . 'contrat_' . $id . '_' . time() . '.pdf';

        file_put_contents($tmpHtml, $html);

        $chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
        if (!file_exists($chrome)) {
            $chrome = 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe';
        }

        $fileUrl = 'file:///' . str_replace('\\', '/', $tmpHtml);

        $cmd = '"' . $chrome . '"'
             . ' --headless'
             . ' --disable-gpu'
             . ' --no-sandbox'
             . ' --disable-software-rasterizer'
             . ' --print-to-pdf="' . $tmpPdf . '"'
             . ' --print-to-pdf-no-header'
             . ' --no-margins'
             . ' "' . $fileUrl . '"'
             . ' 2>&1';

        shell_exec($cmd);

        if (!file_exists($tmpPdf) || filesize($tmpPdf) === 0) {
            unlink($tmpHtml);
            return new Response(
                'Erreur : PDF non généré. Vérifiez que Google Chrome est installé dans "C:\\Program Files\\Google\\Chrome\\Application\\".',
                500
            );
        }

        $pdfContent = file_get_contents($tmpPdf);
        unlink($tmpHtml);
        unlink($tmpPdf);

        $nomSponsor = preg_replace('/[^a-zA-Z0-9_-]/', '_', $sponsor->getNomSponsor());
        $date       = (new \DateTime())->format('d-m-Y');

        return new Response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Contrat_' . $nomSponsor . '_' . $date . '.pdf"',
            'Content-Length'      => strlen($pdfContent),
        ]);
    }

    // ========================
    // TEST MAIL
    // ========================
    #[Route('/test-mail', name: 'test_mail')]
    public function testMail(MailerInterface $mailer): Response
    {
        try {
            $email = (new Email())
                ->from('rokitube8@gmail.com')
                ->to('rokitube8@gmail.com')
                ->subject('Test Simple')
                ->text('Si tu reçois ceci, la configuration email fonctionne !');

            $mailer->send($email);
            return new Response('✅ Email test envoyé !');

        } catch (\Exception $e) {
            return new Response('❌ Erreur : ' . $e->getMessage());
        }
    }
}
