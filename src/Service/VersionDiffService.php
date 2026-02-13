<?php

namespace App\Service;

use App\Entity\Chapitre;
use App\Entity\ChapitreVersion;

class VersionDiffService
{
    /**
     * Calculate the difference between two versions
     */
    public function calculateDiff(Chapitre $current, ?ChapitreVersion $previous): array
    {
        $changes = [];
        $totalChanges = 0;
        $totalFields = 0;

        // Compare title
        $totalFields++;
        if ($previous && $current->getTitre() !== $previous->getTitre()) {
            $changes['titre'] = [
                'old' => $previous->getTitre(),
                'new' => $current->getTitre(),
                'type' => 'modified'
            ];
            $totalChanges++;
        }

        // Compare content (most important)
        $totalFields++;
        if ($previous && $current->getContenu() !== $previous->getContenu()) {
            $similarity = $this->calculateTextSimilarity(
                $previous->getContenu() ?? '', 
                $current->getContenu() ?? ''
            );
            
            $changes['contenu'] = [
                'old' => $previous->getContenu(),
                'new' => $current->getContenu(),
                'type' => 'modified',
                'similarity' => $similarity,
                'change_percentage' => 100 - $similarity
            ];
            $totalChanges++;
        }

        // Compare order
        $totalFields++;
        if ($previous && $current->getOrdre() !== $previous->getOrdre()) {
            $changes['ordre'] = [
                'old' => $previous->getOrdre(),
                'new' => $current->getOrdre(),
                'type' => 'modified'
            ];
            $totalChanges++;
        }

        // Compare content type
        $totalFields++;
        if ($previous && $current->getContentType() !== $previous->getContentType()) {
            $changes['contentType'] = [
                'old' => $previous->getContentType(),
                'new' => $current->getContentType(),
                'type' => 'modified'
            ];
            $totalChanges++;
        }

        // Compare video URL
        $totalFields++;
        if ($previous && $current->getVideoUrl() !== $previous->getVideoUrl()) {
            $changes['videoUrl'] = [
                'old' => $previous->getVideoUrl(),
                'new' => $current->getVideoUrl(),
                'type' => 'modified'
            ];
            $totalChanges++;
        }

        // Compare image URL
        $totalFields++;
        if ($previous && $current->getImageUrl() !== $previous->getImageUrl()) {
            $changes['imageUrl'] = [
                'old' => $previous->getImageUrl(),
                'new' => $current->getImageUrl(),
                'type' => 'modified'
            ];
            $totalChanges++;
        }

        // Compare duration
        $totalFields++;
        if ($previous && $current->getDurationMinutes() !== $previous->getDurationMinutes()) {
            $changes['durationMinutes'] = [
                'old' => $previous->getDurationMinutes(),
                'new' => $current->getDurationMinutes(),
                'type' => 'modified'
            ];
            $totalChanges++;
        }

        // Calculate overall modification percentage
        $modificationPercentage = $totalFields > 0 ? ($totalChanges / $totalFields) * 100 : 0;

        // If content changed, weight it more heavily
        if (isset($changes['contenu'])) {
            $contentChangeWeight = $changes['contenu']['change_percentage'] * 0.7; // 70% weight to content
            $otherChangesWeight = $modificationPercentage * 0.3; // 30% weight to other fields
            $modificationPercentage = $contentChangeWeight + $otherChangesWeight;
        }

        return [
            'changes' => $changes,
            'modification_percentage' => round($modificationPercentage, 2),
            'is_major_change' => $modificationPercentage >= 30,
            'total_changes' => $totalChanges,
            'total_fields' => $totalFields
        ];
    }

    /**
     * Calculate text similarity using Levenshtein distance
     */
    private function calculateTextSimilarity(string $text1, string $text2): float
    {
        if ($text1 === $text2) {
            return 100.0;
        }

        if (empty($text1) || empty($text2)) {
            return 0.0;
        }

        $maxLength = max(strlen($text1), strlen($text2));
        
        // For very long texts, use a sampling approach
        if ($maxLength > 10000) {
            $text1 = substr($text1, 0, 10000);
            $text2 = substr($text2, 0, 10000);
            $maxLength = 10000;
        }

        $distance = levenshtein($text1, $text2);
        $similarity = (1 - ($distance / $maxLength)) * 100;

        return max(0, min(100, $similarity));
    }

    /**
     * Generate human-readable change summary
     */
    public function generateChangeSummary(array $diff): string
    {
        $changes = $diff['changes'];
        $summary = [];

        if (isset($changes['titre'])) {
            $summary[] = "Title changed";
        }

        if (isset($changes['contenu'])) {
            $changePercent = $changes['contenu']['change_percentage'];
            if ($changePercent > 70) {
                $summary[] = "Major content rewrite ({$changePercent}% changed)";
            } elseif ($changePercent > 30) {
                $summary[] = "Significant content update ({$changePercent}% changed)";
            } else {
                $summary[] = "Minor content edits ({$changePercent}% changed)";
            }
        }

        if (isset($changes['ordre'])) {
            $summary[] = "Chapter order updated";
        }

        if (isset($changes['contentType'])) {
            $summary[] = "Content type changed";
        }

        if (isset($changes['videoUrl'])) {
            $summary[] = "Video URL updated";
        }

        if (isset($changes['imageUrl'])) {
            $summary[] = "Image URL updated";
        }

        if (isset($changes['durationMinutes'])) {
            $summary[] = "Duration modified";
        }

        if (empty($summary)) {
            return "No changes detected";
        }

        return implode(', ', $summary);
    }

    /**
     * Get detailed line-by-line diff for content
     */
    public function getDetailedContentDiff(string $old, string $new): array
    {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);

        $diff = [];
        $maxLines = max(count($oldLines), count($newLines));

        for ($i = 0; $i < $maxLines; $i++) {
            $oldLine = $oldLines[$i] ?? '';
            $newLine = $newLines[$i] ?? '';

            if ($oldLine !== $newLine) {
                $diff[] = [
                    'line' => $i + 1,
                    'old' => $oldLine,
                    'new' => $newLine,
                    'type' => empty($oldLine) ? 'added' : (empty($newLine) ? 'removed' : 'modified')
                ];
            }
        }

        return $diff;
    }
}
