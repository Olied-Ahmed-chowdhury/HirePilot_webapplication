<?php
/**
 * HirePilot AI — AI Drafting Service Layer
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Integrates with Grok (X.AI) or any OpenAI-compatible Chat Completions API.
 * Includes a rich, deterministic local mock fallback when API key is missing or offline.
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
        $this->provider    = (string) config('ai.provider', 'mock');
        $this->apiKey      = (string) config('ai.api_key', '');
        $this->apiUrl      = (string) config('ai.api_url', 'https://api.x.ai/v1/chat/completions');
        $this->model       = (string) config('ai.model', 'grok-beta');
        $this->maxTokens   = (int) config('ai.max_tokens', 1200);
        $this->temperature = (float) config('ai.temperature', 0.7);
    }

    /**
     * Generate tailored application content (cover letter or email).
     *
     * @param array  $user       User profile details
     * @param array  $job        Job listing details
     * @param string $resumeText Text parsed from candidate's resume
     * @param string $draftType  'cover_letter' | 'application_email'
     * @param string $tone       'professional' | 'enthusiastic' | 'concise'
     * @return array ['subject' => string, 'body' => string, 'model_used' => string, 'prompt' => string]
     */
    public function generateDraft(
        array $user,
        array $job,
        string $resumeText = '',
        string $draftType = 'cover_letter',
        string $tone = 'professional'
    ): array {
        $prompt = $this->buildPrompt($user, $job, $resumeText, $draftType, $tone);

        // If provider is set to grok/openai and API key is present, try live API call
        if (($this->provider === 'grok' || $this->provider === 'openai') && !empty($this->apiKey)) {
            $response = $this->callApi($prompt, $draftType);
            if ($response !== null) {
                return [
                    'subject'    => $response['subject'],
                    'body'       => $response['body'],
                    'model_used' => $this->model,
                    'prompt'     => $prompt,
                ];
            }
        }

        // Deterministic fallback mock generation
        return $this->generateLocalMock($user, $job, $resumeText, $draftType, $tone, $prompt);
    }

    /**
     * Call Grok / OpenAI Chat Completions API via cURL.
     */
    private function callApi(string $prompt, string $draftType): ?array
    {
        $payload = [
            'model'       => $this->model,
            'messages'    => [
                [
                    'role'    => 'system',
                    'content' => 'You are HirePilot AI, an elite career coach and executive recruiter. You write persuasive, highly tailored, professional job applications that stand out to hiring managers. Output format: Start with "SUBJECT: [Your Subject Line]" on the first line, followed by two newlines, then the complete body text.',
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
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$result) {
            return null;
        }

        $data = json_decode((string)$result, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        if (empty($content)) {
            return null;
        }

        // Parse subject and body
        $subject = 'Application for ' . ($job['title'] ?? 'Role');
        $body    = $content;

        if (preg_match('/^SUBJECT:\s*(.+)$/mi', $content, $matches)) {
            $subject = trim($matches[1]);
            $body    = trim(str_replace($matches[0], '', $content));
        }

        return ['subject' => $subject, 'body' => $body];
    }

    /**
     * Build the structured prompt for LLM.
     */
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

        return <<<PROMPT
Create an outstanding, highly tailored {$docType} with a {$tone} tone.

[CANDIDATE DETAILS]
Name: {$user['name']}
Email: {$user['email']}
Current Headline: {$user['headline']}
Target Role: {$user['target_role']}
Experience: {$user['experience_yrs']} years
Skills: {$skills}
Location: {$user['location']}
Resume Excerpt:
{$resumeText}

[JOB DETAILS]
Job Title: {$job['title']}
Company: {$job['company']}
Location: {$job['location']}
Job Type: {$job['job_type']}
Required Skills: {$reqSkills}
Description:
{$job['description']}
Requirements:
{$job['requirements']}

Format requirements:
- Line 1: SUBJECT: [Tailored Subject Line]
- Body: 3-4 structured paragraphs highlighting relevant accomplishments, skill alignment, passion for the company, and a clear call-to-action.
- Sign off with candidate's full name.
PROMPT;
    }

    /**
     * Deterministic local fallback generator.
     */
    private function generateLocalMock(
        array $user,
        array $job,
        string $resumeText,
        string $draftType,
        string $tone,
        string $prompt
    ): array {
        $candidateName = $user['name'] ?? 'Candidate';
        $companyName   = $job['company'] ?? 'the Hiring Team';
        $jobTitle      = $job['title'] ?? 'the advertised position';
        $headline      = $user['headline'] ?? 'Software Developer';

        $skills = [];
        if (!empty($user['skills'])) {
            $skills = is_string($user['skills']) ? (json_decode($user['skills'], true) ?: []) : $user['skills'];
        }
        $primarySkillStr = !empty($skills) ? implode(', ', array_slice($skills, 0, 4)) : 'modern web development';

        if ($draftType === 'application_email') {
            $subject = "Application: {$jobTitle} - {$candidateName}";
            $body = <<<BODY
Dear Hiring Team at {$companyName},

I am writing to express my strong enthusiasm for the {$jobTitle} opportunity. With my proven background as a {$headline} and hands-on expertise in {$primarySkillStr}, I am confident in my ability to immediately add value to your engineering initiatives.

Throughout my work, I have focused on building scalable, reliable, and user-centric systems. Reviewing the requirements for {$companyName}, I noticed a strong emphasis on clean code, collaborative problem-solving, and efficient system architecture — areas where my core competencies and passion strongly align.

I have attached my resume for your review and would welcome the chance to speak with you regarding how my technical skills and proactive mindset can support your team's upcoming milestones.

Thank you for your time and consideration.

Warm regards,

{$candidateName}
{$user['email']}
{$user['phone']}
BODY;
        } else {
            $subject = "Cover Letter: {$candidateName} - {$jobTitle} at {$companyName}";
            $body = <<<BODY
Dear Hiring Manager,

Please accept this letter as an expression of my deep interest in the {$jobTitle} position at {$companyName}. Having closely followed {$companyName}'s impactful work in the industry, I am eager to contribute my skills in {$primarySkillStr} toward your ongoing product development.

In my recent experience as a {$headline}, I have designed and deployed high-performance applications with strict adherence to maintainability, testability, and agile best practices. My ability to quickly learn new domain logic and bridge technical requirements with business goals has consistently enabled teams to meet release deadlines with confidence.

What excites me most about {$companyName} is your dedication to engineering excellence and innovation. I am particularly eager to apply my background in {$primarySkillStr} to help solve complex technical challenges while collaborating closely with your cross-functional team.

I look forward to discussing how my experience and drive make me a strong fit for {$jobTitle}. Thank you for your review and consideration.

Sincerely,

{$candidateName}
{$user['email']}
BODY;
        }

        return [
            'subject'    => $subject,
            'body'       => $body,
            'model_used' => 'hirepilot-ai-mock-engine (deterministic local fallback)',
            'prompt'     => $prompt,
        ];
    }
}
