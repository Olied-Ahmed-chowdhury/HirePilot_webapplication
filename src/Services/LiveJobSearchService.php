<?php
/**
 * HirePilot AI — Live Job Search Service (Fantastic.jobs + Apify LinkedIn)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Fetches real-time ATS, LinkedIn, and remote job listings from Fantastic.jobs
 * and LinkedIn job results from Apify, then normalizes and persists them.
 */

declare(strict_types=1);

namespace HirePilot\Services;

use HirePilot\Models\Job;
use HirePilot\Models\Resume;

class LiveJobSearchService
{
    private string $fantasticKey;
    private string $fantasticUrl;
    private string $apifyToken;
    private string $apifyActor;
    private Job $jobModel;

    public function __construct()
    {
        $this->fantasticKey = (string) config('jobs_api.fantastic_key', '');
        $this->fantasticUrl = (string) config('jobs_api.fantastic_url', 'https://data.fantastic.jobs/v1/active-ats');
        $this->apifyToken   = (string) config('jobs_api.apify_token', '');
        $this->apifyActor   = (string) config('jobs_api.apify_actor', 'bebity~linkedin-jobs-scraper');
        $this->jobModel     = new Job();
    }

    /**
     * Fetch live jobs from Fantastic.jobs Active ATS API and upsert into database.
     *
     * @return array{ok:bool, fetched:int, inserted:int, source:string, error:?string}
     */
    public function syncFromFantasticJobs(int $createdByUserId, string $query = '', int $limit = 15): array
    {
        if ($this->fantasticKey === '') {
            return ['ok' => false, 'fetched' => 0, 'inserted' => 0, 'source' => 'Fantastic.jobs', 'error' => 'Fantastic.jobs API key is not configured.'];
        }

        $fetchLimit = trim($query) !== '' ? 50 : max(1, min(50, $limit));
        $params = [
            'time_frame' => '7d',
            'limit'      => $fetchLimit,
        ];

        $url = $this->fantasticUrl . '?' . http_build_query($params);
        $resp = $this->httpGetJson($url, [
            'Authorization: Bearer ' . $this->fantasticKey,
            'Content-Type: application/json',
        ], 25);

        if (!$resp['ok']) {
            return ['ok' => false, 'fetched' => 0, 'inserted' => 0, 'source' => 'Fantastic.jobs', 'error' => $resp['error']];
        }

        $items = is_array($resp['data']) ? $resp['data'] : [];

        // If user provided a query/resume keywords, prioritize matching live jobs first
        if (trim($query) !== '' && !empty($items)) {
            $terms = array_values(array_filter(preg_split('/[\s,]+/', strtolower(trim($query))) ?: []));
            $matched = [];
            $others  = [];
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $haystack = strtolower(
                    ($item['title'] ?? '') . ' ' .
                    ($item['organization'] ?? '') . ' ' .
                    ($item['ai_core_responsibilities'] ?? '') . ' ' .
                    implode(' ', is_array($item['ai_key_skills'] ?? null) ? $item['ai_key_skills'] : [])
                );
                $hit = false;
                foreach ($terms as $t) {
                    if (strlen($t) >= 2 && str_contains($haystack, $t)) {
                        $hit = true;
                        break;
                    }
                }
                if ($hit) {
                    $matched[] = $item;
                } else {
                    $others[] = $item;
                }
            }
            $items = array_slice(!empty($matched) ? array_merge($matched, $others) : $items, 0, max(1, min(50, $limit)));
        }

        $inserted = 0;
        foreach ($items as $item) {
            if (!is_array($item) || empty($item['title'])) {
                continue;
            }
            $mapped = $this->mapFantasticItem($item, $createdByUserId);
            $res = $this->jobModel->upsertExternal($mapped);
            if ($res['inserted']) {
                $inserted++;
            }
        }

        return [
            'ok'       => true,
            'fetched'  => count($items),
            'inserted' => $inserted,
            'source'   => 'Fantastic.jobs (Live ATS)',
            'error'    => null,
        ];
    }

    /**
     * Fetch similar LinkedIn jobs via Apify LinkedIn Jobs Scraper (with Fantastic.jobs fallback).
     *
     * @return array{ok:bool, fetched:int, inserted:int, source:string, query:string, error:?string}
     */
    public function syncFromApifyLinkedIn(int $createdByUserId, string $keywords, string $location = '', int $limit = 10): array
    {
        $keywords = trim($keywords) !== '' ? trim($keywords) : 'Software Engineer';

        if ($this->apifyToken !== '') {
            $actor = str_replace('/', '~', $this->apifyActor);
            $url = "https://api.apify.com/v2/acts/{$actor}/run-sync-get-dataset-items?token=" . urlencode($this->apifyToken);

            $payload = [
                'keywords' => $keywords,
                'location' => trim($location) !== '' ? trim($location) : 'United States',
                'limit'    => max(1, min(20, $limit)),
            ];

            $resp = $this->httpPostJson($url, $payload, ['Content-Type: application/json'], 20);
            if ($resp['ok'] && is_array($resp['data']) && count($resp['data']) > 0) {
                $inserted = 0;
                foreach ($resp['data'] as $item) {
                    if (!is_array($item)) {
                        continue;
                    }
                    $mapped = $this->mapApifyItem($item, $createdByUserId);
                    if ($mapped === null) {
                        continue;
                    }
                    $res = $this->jobModel->upsertExternal($mapped);
                    if ($res['inserted']) {
                        $inserted++;
                    }
                }

                if (count($resp['data']) > 0) {
                    return [
                        'ok'       => true,
                        'fetched'  => count($resp['data']),
                        'inserted' => $inserted,
                        'source'   => 'Apify LinkedIn Jobs',
                        'query'    => $keywords,
                        'error'    => null,
                    ];
                }
            }
        }

        // Graceful fallback to Fantastic.jobs live ATS/LinkedIn feed using the same resume/profile query
        $fallback = $this->syncFromFantasticJobs($createdByUserId, $keywords, $limit);
        return [
            'ok'       => $fallback['ok'],
            'fetched'  => $fallback['fetched'],
            'inserted' => $fallback['inserted'],
            'source'   => $fallback['ok'] ? 'Fantastic.jobs (Resume Match)' : 'Live Job API',
            'query'    => $keywords,
            'error'    => $fallback['error'],
        ];
    }

    /**
     * Build a targeted search query from the candidate's uploaded resume + profile skills/role.
     */
    public function buildQueryFromResumeAndProfile(int $userId, array $user): string
    {
        $terms = [];

        if (!empty($user['target_role'])) {
            $terms[] = trim((string) $user['target_role']);
        }

        $skills = is_string($user['skills'] ?? '')
            ? (json_decode($user['skills'], true) ?? [])
            : ($user['skills'] ?? []);
        if (is_array($skills) && !empty($skills)) {
            $terms = array_merge($terms, array_slice(array_filter(array_map('trim', $skills)), 0, 3));
        }

        // Check primary resume parsed text or label for extra context
        $resumeModel = new Resume();
        $resumes = $resumeModel->getAllByUser($userId);
        if (!empty($resumes)) {
            $primary = $resumes[0];
            if (!empty($primary['label']) && empty($terms)) {
                $terms[] = trim((string) $primary['label']);
            }
            if (!empty($primary['parsed_text']) && count($terms) < 2) {
                $commonSkills = ['PHP', 'Laravel', 'JavaScript', 'React', 'Node.js', 'Python', 'Java', 'SQL', 'MySQL', 'Docker', 'AWS', 'DevOps', 'Frontend', 'Backend', 'Full Stack', 'Software Engineer'];
                foreach ($commonSkills as $kw) {
                    if (stripos((string) $primary['parsed_text'], $kw) !== false && !in_array($kw, $terms, true)) {
                        $terms[] = $kw;
                        if (count($terms) >= 3) {
                            break;
                        }
                    }
                }
            }
        }

        if (empty($terms)) {
            return 'Software Engineer';
        }

        return implode(' ', array_slice($terms, 0, 2));
    }

    private function mapFantasticItem(array $item, int $createdBy): array
    {
        $arrangement = strtolower((string) ($item['ai_work_arrangement'] ?? ''));
        $isRemote = str_contains($arrangement, 'remote') ? 1 : 0;

        $empTypes = $item['ai_employment_type'] ?? [];
        $rawType  = is_array($empTypes) && !empty($empTypes) ? strtolower((string) $empTypes[0]) : 'full-time';
        $jobType  = match (true) {
            str_contains($rawType, 'part')       => 'part-time',
            str_contains($rawType, 'contract')   => 'contract',
            str_contains($rawType, 'intern')     => 'internship',
            default                              => 'full-time',
        };

        $rawExp = strtolower((string) ($item['ai_experience_level'] ?? 'mid'));
        $expLevel = match (true) {
            str_contains($rawExp, '0-2') || str_contains($rawExp, 'entry') || str_contains($rawExp, 'junior') => 'entry',
            str_contains($rawExp, '5-10') || str_contains($rawExp, 'senior') => 'senior',
            str_contains($rawExp, '10+') || str_contains($rawExp, 'lead')   => 'lead',
            default => 'mid',
        };

        $location = 'Remote';
        if (!empty($item['locations_derived']) && is_array($item['locations_derived'])) {
            $location = (string) $item['locations_derived'][0];
        } elseif (!empty($item['cities_derived']) && is_array($item['cities_derived'])) {
            $location = (string) $item['cities_derived'][0];
        }

        $skills = [];
        if (!empty($item['ai_key_skills']) && is_array($item['ai_key_skills'])) {
            $skills = array_slice(array_values(array_filter(array_map('strval', $item['ai_key_skills']))), 0, 10);
        }

        $desc = trim((string) ($item['ai_core_responsibilities'] ?? ''));
        if ($desc === '') {
            $desc = 'Live opening sourced from ' . ($item['organization'] ?? 'Employer ATS') . ' (' . ($item['source'] ?? 'ATS') . ').';
        }
        $req = trim((string) ($item['ai_requirements_summary'] ?? ''));
        $benefits = !empty($item['ai_benefits']) && is_array($item['ai_benefits'])
            ? implode(', ', array_slice($item['ai_benefits'], 0, 6))
            : null;

        return [
            'created_by'       => $createdBy,
            'title'            => mb_substr(trim((string) $item['title']), 0, 195),
            'company'          => mb_substr(trim((string) ($item['organization'] ?? 'Hiring Company')), 0, 145),
            'location'         => mb_substr($location, 0, 145),
            'is_remote'        => $isRemote,
            'job_type'         => $jobType,
            'experience_level' => $expLevel,
            'salary_min'       => is_numeric($item['ai_salary_min_value'] ?? null) ? (float) $item['ai_salary_min_value'] : null,
            'salary_max'       => is_numeric($item['ai_salary_max_value'] ?? null) ? (float) $item['ai_salary_max_value'] : null,
            'salary_currency'  => mb_substr((string) ($item['ai_salary_currency'] ?? 'USD'), 0, 10),
            'description'      => $desc,
            'requirements'     => $req !== '' ? $req : null,
            'benefits'         => $benefits,
            'skills_required'  => json_encode($skills),
            'external_url'     => !empty($item['url']) ? mb_substr((string) $item['url'], 0, 495) : null,
            'status'           => 'active',
        ];
    }

    private function mapApifyItem(array $item, int $createdBy): ?array
    {
        $title   = trim((string) ($item['title'] ?? $item['positionName'] ?? $item['jobTitle'] ?? ''));
        $company = trim((string) ($item['companyName'] ?? $item['company'] ?? ''));
        if ($title === '' || $company === '') {
            return null;
        }

        $location = trim((string) ($item['location'] ?? 'Remote'));
        $isRemote = stripos($location, 'remote') !== false ? 1 : 0;
        $desc     = trim((string) ($item['description'] ?? $item['descriptionText'] ?? 'Live LinkedIn job listing imported via Apify.'));
        $url      = trim((string) ($item['jobUrl'] ?? $item['url'] ?? $item['link'] ?? ''));

        $rawType = strtolower((string) ($item['contractType'] ?? $item['employmentType'] ?? 'full-time'));
        $jobType = match (true) {
            str_contains($rawType, 'part')     => 'part-time',
            str_contains($rawType, 'contract') => 'contract',
            str_contains($rawType, 'intern')   => 'internship',
            default                            => 'full-time',
        };

        return [
            'created_by'       => $createdBy,
            'title'            => mb_substr($title, 0, 195),
            'company'          => mb_substr($company, 0, 145),
            'location'         => mb_substr($location !== '' ? $location : 'Remote', 0, 145),
            'is_remote'        => $isRemote,
            'job_type'         => $jobType,
            'experience_level' => 'mid',
            'salary_min'       => null,
            'salary_max'       => null,
            'salary_currency'  => 'USD',
            'description'      => mb_substr(strip_tags($desc), 0, 4000),
            'requirements'     => null,
            'benefits'         => null,
            'skills_required'  => json_encode([]),
            'external_url'     => $url !== '' ? mb_substr($url, 0, 495) : null,
            'status'           => 'active',
        ];
    }

    private function httpGetJson(string $url, array $headers, int $timeout = 20): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err !== '' || $code < 200 || $code >= 300 || !$raw) {
            return ['ok' => false, 'data' => null, 'error' => $err !== '' ? $err : "HTTP {$code}"];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'data' => null, 'error' => 'Invalid JSON from job API'];
        }
        return ['ok' => true, 'data' => $decoded, 'error' => null];
    }

    private function httpPostJson(string $url, array $payload, array $headers, int $timeout = 45): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err !== '' || $code < 200 || $code >= 300 || !$raw) {
            return ['ok' => false, 'data' => null, 'error' => $err !== '' ? $err : "HTTP {$code}"];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'data' => null, 'error' => 'Invalid JSON from Apify API'];
        }
        return ['ok' => true, 'data' => $decoded, 'error' => null];
    }
}
