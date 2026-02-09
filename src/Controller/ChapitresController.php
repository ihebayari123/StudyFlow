<?php

namespace App\Controller;

use App\Entity\Chapitre;
use App\Entity\Cours;
use App\Repository\CoursRepository;
use App\Repository\ChapitreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ChapitresController extends AbstractController
{
    #[Route('/chapitres', name: 'app_chapitres_index')]
    public function index(ChapitreRepository $chapitreRepository): Response
    {
        $chapitres = $chapitreRepository->findAll();
        
        return $this->render('chapitres/index.html.twig', [
            'controller_name' => 'ChapitresController',
            'chapitres' => $chapitres,
        ]);
    }

    #[Route('/cours/{id}/chapitres', name: 'app_cours_chapitres')]
    public function showChapitres(int $id, CoursRepository $coursRepository): Response
    {
        $cours = $coursRepository->find($id);
        
        if (!$cours) {
            throw $this->createNotFoundException('Course not found');
        }
        
        return $this->render('chapitres/chapitresfront.html.twig', [
            'cours' => $cours,
            'chapitres' => $cours->getChapitres(),
        ]);
    }

    #[Route('/admin/courses/{courseId}/chapters', name: 'app_chapitres')]
    public function showChapitresByCourse(
        int $courseId, 
        CoursRepository $coursRepository
    ): Response
    {
        $cours = $coursRepository->find($courseId);
        
        if (!$cours) {
            $this->addFlash('error', 'Course not found');
            return $this->redirectToRoute('app_coursesback');
        }

        $chapitres = $cours->getChapitres();

        return $this->render('chapitres/chapitre.html.twig', [
            'cours' => $cours,
            'chapitres' => $chapitres,
        ]);
    }

    #[Route('/admin/courses/{courseId}/chapters/add', name: 'app_addchapitre', methods: ['GET', 'POST'])]
public function addChapitre(Request $request, int $courseId, EntityManagerInterface $entityManager): Response
{
    $cours = $entityManager->getRepository(Cours::class)->find($courseId);
    
    if (!$cours) {
        throw $this->createNotFoundException('Course not found');
    }

    if ($request->isMethod('POST')) {
        $chapitre = new Chapitre();
        
        // Set basic fields
        $chapitre->setTitre($request->request->get('titre'));
        $chapitre->setOrdre((int)$request->request->get('ordre'));
        $chapitre->setContenu($request->request->get('contenu'));
        $chapitre->setContentType($request->request->get('contentType'));
        $chapitre->setVideoUrl($request->request->get('videoUrl'));
        $chapitre->setImageUrl($request->request->get('imageUrl'));
        
        // Set duration if provided
        if ($request->request->get('durationMinutes')) {
            $chapitre->setDurationMinutes((int)$request->request->get('durationMinutes'));
        }
        
        // Handle file upload
        $file = $request->files->get('fileUpload');
if ($file) {
    // Generate unique filename
    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    // Sanitize filename - remove special characters
    $safeFilename = preg_replace('/[^A-Za-z0-9-]/', '_', $originalFilename);
    $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
    
    // Move file to uploads directory
    try {
        $file->move(
            $this->getParameter('chapters_directory'),
            $newFilename
        );
        $chapitre->setFileName($newFilename);
    } catch (FileException $e) {
        $this->addFlash('error', 'Error uploading file: ' . $e->getMessage());
    }
}
        
        // Handle additional links
        $linkTitles = $request->request->all('linkTitle');
        $linkUrls = $request->request->all('linkUrl');
        $links = [];
        
        if ($linkTitles && $linkUrls) {
            foreach ($linkTitles as $index => $title) {
                if (!empty($title) && !empty($linkUrls[$index])) {
                    $links[] = [
                        'title' => $title,
                        'url' => $linkUrls[$index]
                    ];
                }
            }
        }
        
        if (!empty($links)) {
            $chapitre->setLinks($links);
        }
        
        // Associate with course
        $chapitre->setCourse($cours);
        
        // Persist and flush
        $entityManager->persist($chapitre);
        $entityManager->flush();
        
        $this->addFlash('success', 'Chapter added successfully!');
        return $this->redirectToRoute('app_chapitres', ['courseId' => $courseId]);
    }
    
    return $this->render('chapitres/add.html.twig', [
        'cours' => $cours,
    ]);
}

    #[Route('/admin/chapters/{id}/edit', name: 'app_editchapitre', methods: ['GET', 'POST'])]
    public function editChapitre(
        int $id, 
        Request $request,
        ChapitreRepository $chapitreRepository,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response
    {
        $chapitre = $chapitreRepository->find($id);
        
        if (!$chapitre) {
            $this->addFlash('error', 'Chapter not found');
            return $this->redirectToRoute('app_coursesback');
        }

        if ($request->isMethod('POST')) {
            $chapitre->setTitre($request->request->get('titre'));
            $chapitre->setContenu($request->request->get('contenu'));
            $chapitre->setOrdre((int)$request->request->get('ordre'));
            
            $chapitre->setContentType($request->request->get('contentType'));
            $chapitre->setVideoUrl($request->request->get('videoUrl'));
            $chapitre->setImageUrl($request->request->get('imageUrl'));
            
            $duration = $request->request->get('durationMinutes');
            $chapitre->setDurationMinutes($duration ? (int)$duration : null);
            
            $uploadedFile = $request->files->get('file');
            if ($uploadedFile) {
                if ($chapitre->getFileName()) {
                    $oldFile = $this->getParameter('kernel.project_dir') . '/public/uploads/chapters/' . $chapitre->getFileName();
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

                try {
                    $uploadedFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/chapters',
                        $newFilename
                    );
                    $chapitre->setFileName($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                }
            }
            
            $linkTitles = $request->request->all('linkTitle');
            $linkUrls = $request->request->all('linkUrl');
            $links = [];
            
            if ($linkTitles && $linkUrls) {
                foreach ($linkTitles as $index => $title) {
                    if (!empty($title) && !empty($linkUrls[$index])) {
                        $links[] = [
                            'title' => $title,
                            'url' => $linkUrls[$index]
                        ];
                    }
                }
            }
            
            $chapitre->setLinks(!empty($links) ? $links : null);

            $em->flush();

            $this->addFlash('success', 'Chapter updated successfully');
            return $this->redirectToRoute('app_chapitres', ['courseId' => $chapitre->getCourse()->getId()]);
        }

        return $this->render('chapitres/edit.html.twig', [
            'chapitre' => $chapitre,
        ]);
    }

    #[Route('/admin/chapters/{id}/delete', name: 'app_deletechapitre')]
    public function deleteChapitre(
        int $id, 
        ChapitreRepository $chapitreRepository,
        EntityManagerInterface $em
    ): Response
    {
        $chapitre = $chapitreRepository->find($id);
        
        if (!$chapitre) {
            $this->addFlash('error', 'Chapter not found');
            return $this->redirectToRoute('app_coursesback');
        }

        if ($chapitre->getFileName()) {
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/chapters/' . $chapitre->getFileName();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $courseId = $chapitre->getCourse()->getId();
        
        $em->remove($chapitre);
        $em->flush();
        
        $this->addFlash('success', 'Chapter deleted successfully');
        return $this->redirectToRoute('app_chapitres', ['courseId' => $courseId]);
    }
}