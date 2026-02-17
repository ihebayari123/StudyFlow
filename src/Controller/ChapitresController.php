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
use App\Repository\ChapitreVersionRepository;
use App\Service\VersionDiffService;
use App\Entity\ChapitreVersion;
use Doctrine\Persistence\ManagerRegistry;

final class ChapitresController extends AbstractController
{

 private VersionDiffService $diffService;

    public function __construct(VersionDiffService $diffService)
    {
        $this->diffService = $diffService;
    }
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

    #[Route('/admin/chapter/{id}/edit', name: 'app_editchapitre')]
    public function editChapitre(
        int $id,
        Request $request,
        ManagerRegistry $doctrine,
        ChapitreRepository $chapitreRepository,
        ChapitreVersionRepository $versionRepository
    ): Response {
        $em = $doctrine->getManager();
        $chapitre = $chapitreRepository->find($id);

        if (!$chapitre) {
            throw $this->createNotFoundException('Chapter not found');
        }

        if ($request->isMethod('POST')) {
            // Store the old version before updating
            $this->createVersionSnapshot($chapitre, $em, $versionRepository);

            // Update basic fields
            $chapitre->setTitre($request->request->get('titre'));
            $chapitre->setContenu($request->request->get('contenu'));
            $chapitre->setOrdre((int)$request->request->get('ordre'));
            
            // Update new fields
            $chapitre->setContentType($request->request->get('contentType'));
            $chapitre->setVideoUrl($request->request->get('videoUrl'));
            $chapitre->setImageUrl($request->request->get('imageUrl'));
            
            // Duration
            if ($request->request->get('durationMinutes')) {
                $chapitre->setDurationMinutes((int)$request->request->get('durationMinutes'));
            }
            
            // Handle file upload
            $uploadedFile = $request->files->get('fileUpload');
            if ($uploadedFile) {
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();
                
                try {
                    $uploadedFile->move(
                        $this->getParameter('uploads_directory'), // You need to configure this
                        $newFilename
                    );
                    $chapitre->setFileName($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'File upload failed: ' . $e->getMessage());
                }
            }
            
            // Handle links
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
            
            $chapitre->setLinks($links);

            $em->flush();

            $this->addFlash('success', 'Chapter updated successfully! Version saved.');
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

    #[Route('/admin/chapter/{id}/versions', name: 'app_chapitre_versions')]
    public function viewVersionHistory(
        int $id,
        ChapitreRepository $chapitreRepository,
        ChapitreVersionRepository $versionRepository
    ): Response {
        $chapitre = $chapitreRepository->find($id);

        if (!$chapitre) {
            throw $this->createNotFoundException('Chapter not found');
        }

        $versions = $versionRepository->findByChapitreOrderedByVersion($chapitre);
        $statistics = $versionRepository->getVersionStatistics($chapitre);

        return $this->render('courses/version_history.html.twig', [
            'chapitre' => $chapitre,
            'versions' => $versions,
            'statistics' => $statistics,
        ]);
    }

    #[Route('/admin/chapter/version/{versionId}/compare', name: 'app_compare_version')]
    public function compareVersions(
        int $versionId,
        ChapitreVersionRepository $versionRepository,
        ChapitreRepository $chapitreRepository
    ): Response {
        $version = $versionRepository->find($versionId);

        if (!$version) {
            throw $this->createNotFoundException('Version not found');
        }

        $currentChapitre = $version->getChapitre();
        
        // Get previous version
        $previousVersion = $versionRepository->findVersionByNumber(
            $currentChapitre,
            $version->getVersionNumber() - 1
        );

        // Calculate detailed diff
        $contentDiff = null;
        if ($previousVersion) {
            $contentDiff = $this->diffService->getDetailedContentDiff(
                $previousVersion->getContenu() ?? '',
                $version->getContenu() ?? ''
            );
        }

        return $this->render('courses/compare_versions.html.twig', [
            'version' => $version,
            'previousVersion' => $previousVersion,
            'currentChapitre' => $currentChapitre,
            'contentDiff' => $contentDiff,
        ]);
    }

    #[Route('/admin/chapter/version/{versionId}/restore', name: 'app_restore_version')]
    public function restoreVersion(
        int $versionId,
        ManagerRegistry $doctrine,
        ChapitreVersionRepository $versionRepository
    ): Response {
        $em = $doctrine->getManager();
        $version = $versionRepository->find($versionId);

        if (!$version) {
            throw $this->createNotFoundException('Version not found');
        }

        $chapitre = $version->getChapitre();

        // Create a version snapshot of current state before restoring
        $this->createVersionSnapshot($chapitre, $em, $versionRepository);

        // Restore the version
        $chapitre->setTitre($version->getTitre());
        $chapitre->setContenu($version->getContenu());
        $chapitre->setOrdre($version->getOrdre());
        $chapitre->setContentType($version->getContentType());
        $chapitre->setVideoUrl($version->getVideoUrl());
        $chapitre->setFileName($version->getFileName());
        $chapitre->setLinks($version->getLinks());
        $chapitre->setImageUrl($version->getImageUrl());
        $chapitre->setDurationMinutes($version->getDurationMinutes());

        $em->flush();

        $this->addFlash('success', "Chapter restored to version {$version->getVersionNumber()} successfully!");
        return $this->redirectToRoute('app_chapitre_versions', ['id' => $chapitre->getId()]);
    }

    /**
     * Create a version snapshot before updating
     */
    private function createVersionSnapshot(
        Chapitre $chapitre,
        $entityManager,
        ChapitreVersionRepository $versionRepository
    ): void {
        // Get the latest version number
        $latestVersionNumber = $versionRepository->getLatestVersionNumber($chapitre);
        
        // Get the previous version for comparison
        $previousVersion = null;
        if ($latestVersionNumber > 0) {
            $previousVersion = $versionRepository->findVersionByNumber($chapitre, $latestVersionNumber);
        }

        // Calculate differences
        $diff = $this->diffService->calculateDiff($chapitre, $previousVersion);

        // Create new version
        $version = new ChapitreVersion();
        $version->setChapitre($chapitre);
        $version->setVersionNumber($latestVersionNumber + 1);
        $version->setTitre($chapitre->getTitre());
        $version->setContenu($chapitre->getContenu());
        $version->setOrdre($chapitre->getOrdre());
        $version->setContentType($chapitre->getContentType());
        $version->setVideoUrl($chapitre->getVideoUrl());
        $version->setFileName($chapitre->getFileName());
        $version->setLinks($chapitre->getLinks());
        $version->setImageUrl($chapitre->getImageUrl());
        $version->setDurationMinutes($chapitre->getDurationMinutes());
        $version->setChangeDescription($this->diffService->generateChangeSummary($diff));
        $version->setChangesDetected($diff['changes']);
        $version->setModificationPercentage($diff['modification_percentage']);
        
        // You can set this from the logged-in user if you have authentication
        // $version->setModifiedBy($this->getUser()->getUsername());
        $version->setModifiedBy('Admin'); // Placeholder

        $entityManager->persist($version);
    }
}