<?php
/**
 * HirePilot AI — AI Drafting Service Layer (Groq / Grok Compatible)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Services;

class AiDraftingService
{
    private string $provider;
    private string $apiKey;
    private string $apiUrl;
    private string $model;
    private int $maxTokens;
    private float $temperature;

    public function __construct()
    {
        $this->provider    = (string) config('ai.provider', 'groq');
        $this->apiKey      = (string) config('ai.api_key', '');
        $this->apiUrl      = (string) config('ai.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $this->model       = (string) config('ai.model', 'llama-3.3-70b-versatile');
        $this->maxTokens   = (int) config('ai.max_tokens', 1200);
        $this->temperature = (float) config('ai.temperature', 0.6);
    }

    public function generateDraft(
        array $user,
        array $job,
        string $resumeText = '',
        string $draftType = 'cover_letter',
        string $tone = 'professional'
    ): array {
        $prompt = $this->buildPrompt($user, $job, $resumeText, $draftType, $tone);

        if ($this->provider !== 'mock' && !empty($this->apiKey)) {
            $response = $this->callApi($prompt, $job);
            if ($response !== null) {
                return [
                    'subject'    => $response['subject'],
                    'body'       => $response['body'],
                    'model_used' => $this->model,
                    'prompt'     => $prompt,
                ];
            }
        }

        return $this->generateLocalFallback($user, $job, $draftType, $prompt);
    }

    private function callApi(string $prompt, array $job): ?array
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'You are a senior career strategist writing concise, factual, tailored job application emails and cover letters. Avoid clichés and filler. Output format: Start line 1 with "SUBJECT: <subject line>", then a blank line, then the application text.',
                ],
                [
                    'role'    => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens'  => $this->maxTokens,
            'temperature' => $this->temperature,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result   = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300 || !$result) {
            return null;
        }

        $data    = json_decode((string) $result, true);
        $content = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
        if ($content === '') {
            return null;
        }

        $subject = 'Application: ' . ($job['title'] ?? 'Position');
        $body    = $content;

        if (preg_match('/^SUBJECT:\s*(.+)$/mi', $content, $matches)) {
            $subject = trim($matches[1]);
            $body    = trim(str_replace($matches[0], '', $content));
        }

        return ['subject' => $subject, 'body' => $body];
    }

    private function buildPrompt(
        array $user,
        array $job,
        string $resumeText,
        string $draftType,
        string $tone
    ): string {
        $skills = !empty($user['skills'])
            ? (is_string($user['skills']) ? $user['skills'] : json_encode($user['skills']))
            : '[]';

        $reqSkills = !empty($job['skills_required'])
            ? (is_string($job['skills_required']) ? $job['skills_required'] : json_encode($job['skills_required']))
            : '[]';

        $docType = $draftType === 'cover_letter' ? 'Cover Letter' : 'Direct Application Email';
        $resumeExcerpt = mb_substr(trim($resumeText), 0, 2000);

        return <<<PROMPT
Write a tailored {$docType} in a {$tone} tone.

Candidate:
- Name: {$user['name']}
- Email: {$user['email']}
- Headline: {$user['headline']}
- Target Role: {$user['target_role']}
- Experience: {$user['experience_yrs']} years
- Skills: {$skills}
- Resume Excerpt: {$resumeExcerpt}

Target Role:
- Title: {$job['title']}
- Company: {$job['company']}
- Location: {$job['location']}
- Required Skills: {$reqSkills}
- Description: {$job['description']}
- Requirements: {$job['requirements']}
PROMPT;
    }

    private function generateLocalFallback(array $user, array $job, string $draftType, string $prompt): array
    {
        $name    = $user['name'] ?? 'Candidate';
        $company = $job['company'] ?? 'Hiring Team';
        $title   = $job['title'] ?? 'Role';
        $skills  = is_string($user['skills'] ?? '') ? (json_decode($user['skills'], true) ?? []) : ($user['skills'] ?? []);
        $skillStr = !empty($skills) ? implode(', ', array_slice($skills, 0, 5)) : 'software engineering';

        $subject = "Application for {$title} — {$name}";
        $body = $draftType === 'application_email'
            ? "Dear {$company} Hiring Team,\n\nI am writing to apply for the {$title} position. My technical background includes {$skillStr}, and I have hands-on experience delivering maintainable web applications.\n\nI have attached my resume for your consideration and welcome the opportunity to discuss how my skills align with your engineering goals.\n\nBest regards,\n{$name}\n{$user['email']}"
            : "Dear Hiring Manager,\n\nI am pleased to submit my application for the {$title} role at {$company}. With practical experience in {$skillStr}, I am confident in my ability to contribute effectively to your team.\n\nIn reviewing your requirements for {$title}, my focus on clean architecture, database integrity, and collaborative delivery aligns closely with what {$company} is building.\n\nThank you for your time and consideration.\n\nSincerely,\n{$name}\n{$user['email']}";

        return [
            'subject'    => $subject,
            'body'       => $body,
            'model_used' => 'local-template-fallback',
            'prompt'     => $prompt,
        ];
    }
}
