<?php
/**
 * HirePilot AI — Job Matching & Relevance Scoring Service
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Calculates a match score (0–100%) between a candidate profile
 * (skills, remote preference, experience) and a job opening.
 */

declare(strict_types=1);

namespace HirePilot\Services;

class JobMatchingService
{
    /**
     * Compute a relevance score (0 - 100) for a job against a user profile.
     */
    public function calculateScore(array $job, array $userSkills, string $remotePref = 'any'): int
    {
        $score = 0;

        // 1. Skill overlap (Max 70 points)
        $requiredSkills = [];
        if (!empty($job['skills_required'])) {
            $requiredSkills = is_string($job['skills_required'])
                ? (json_decode($job['skills_required'], true) ?: [])
                : $job['skills_required'];
        }

        if (!empty($requiredSkills) && !empty($userSkills)) {
            $normalizedUser = array_map('strtolower', array_map('trim', $userSkills));
            $normalizedReq  = array_map('strtolower', array_map('trim', $requiredSkills));

            $matches = 0;
            foreach ($normalizedReq as $req) {
                foreach ($normalizedUser as $userSkill) {
                    if ($req === $userSkill || str_contains($userSkill, $req) || str_contains($req, $userSkill)) {
                        $matches++;
                        break;
                    }
                }
            }

            $overlapRatio = $matches / count($normalizedReq);
            $score += (int) round($overlapRatio * 70);
        } elseif (empty($requiredSkills)) {
            $score += 45; // baseline if no specific skills specified
        }

        // 2. Remote preference match (Max 20 points)
        $isRemoteJob = (bool)($job['is_remote'] ?? false);
        if ($remotePref === 'any') {
            $score += 20;
        } elseif ($remotePref === 'remote' && $isRemoteJob) {
            $score += 20;
        } elseif ($remotePref === 'hybrid') {
            $score += 15;
        } elseif ($remotePref === 'onsite' && !$isRemoteJob) {
            $score += 20;
        } else {
            $score += 5;
        }

        // 3. Baseline activity bonus (Max 10 points)
        if (($job['status'] ?? 'active') === 'active') {
            $score += 10;
        }

        return min(100, max(15, $score));
    }

    /**
     * Sort an array of jobs by calculated relevance score descending.
     */
    public function rankJobs(array $jobs, array $userSkills, string $remotePref = 'any'): array
    {
        foreach ($jobs as &$job) {
            $job['match_score'] = $this->calculateScore($job, $userSkills, $remotePref);
        }
        unset($job);

        usort($jobs, fn($a, $b) => ($b['match_score'] ?? 0) <=> ($a['match_score'] ?? 0));
        return $jobs;
    }
}
