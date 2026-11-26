<?php
/**
 * HirePilot AI — Multi-Turn AI Profile Inquiry Service (SRS 2.3)
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Conducts a focused multi-turn inquiry via Grok/Groq API to gather
 * missing profile fields, keeping all external HTTP calls outside DB transactions.
 */

declare(strict_types=1);

namespace HirePilot\Services;

class AiInquiryService
{
    private string $apiKey;
    private string $apiUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('ai.api_key', '');
        $this->apiUrl = (string) config('ai.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $this->model  = (string) config('ai.model', 'llama-3.3-70b-versatile');
    }

    public function getMissingFields(array $user): array
    {
        $missing = [];
        if (empty($user['target_role'])) {
            $missing[] = 'target_role';
        }
        if (empty($user['experience_level'])) {
            $missing[] = 'experience_level';
        }
        $skills = is_string($user['skills'] ?? '') ? (json_decode($user['skills'], true) ?? []) : ($user['skills'] ?? []);
        if (empty($skills)) {
            $missing[] = 'skills';
        }
        if (empty($user['work_preference']) || $user['work_preference'] === 'any') {
            $missing[] = 'work_preference';
        }
        if (empty($user['preferred_location'])) {
            $missing[] = 'preferred_location';
        }
        if (empty($user['career_goals'])) {
            $missing[] = 'career_goals';
        }
        return $missing;
    }

    /**
     * Process a user reply and generate the next focused follow-up question.
     * Returns ['reply' => string, 'extracted' => array, 'completed' => bool, 'api_error' => ?string]
     */
    public function processTurn(array $user, array $history, string $userMessage): array
    {
        $missing = $this->getMissingFields($user);

        if (empty($this->apiKey)) {
            return [
                'reply'     => null,
                'extracted' => [],
                'completed' => empty($missing),
                'api_error' => 'AI API key is not configured. Please complete your profile fields manually below.',
            ];
        }

        $currentProfile = [
            'target_role'        => $user['target_role'] ?? '',
            'experience_level'   => $user['experience_level'] ?? '',
            'skills'             => is_string($user['skills'] ?? '') ? (json_decode($user['skills'], true) ?? []) : ($user['skills'] ?? []),
            'work_preference'    => $user['work_preference'] ?? 'any',
            'preferred_location' => $user['preferred_location'] ?? '',
            'career_goals'       => $user['career_goals'] ?? '',
        ];

        $systemPrompt = <<<PROMPT
You are HirePilot AI's career onboarding assistant. Your job is to interview the candidate concisely to complete their profile.
Current known profile:
{PROFILE_JSON}

Missing fields: {MISSING_LIST}

Analyze the user's latest message and conversation history.
Return ONLY valid JSON (no markdown fences) with this exact structure:
{
  "extracted": {
    "target_role": "string or null if not mentioned",
    "experience_level": "entry|mid|senior|lead or null",
    "skills": ["array of new skill strings or empty array"],
    "work_preference": "remote|hybrid|onsite|any or null",
    "preferred_location": "string or null",
    "career_goals": "string or null"
  },
  "follow_up_question": "One concise, professional follow-up question asking for the remaining missing fields, or a brief confirmation message if all core fields are now filled.",
  "is_complete": true or false
}
PROMPT;

        $systemPrompt = str_replace(
            ['{PROFILE_JSON}', '{MISSING_LIST}'],
            [json_encode($currentProfile), implode(', ', $missing) ?: 'none'],
            $systemPrompt
        );

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach (array_slice($history, -8) as $turn) {
            $messages[] = [
                'role'    => $turn['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => (string) $turn['content'],
            ];
        }
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $payload = [
            'model'       => $this->model,
            'messages'    => $messages,
            'max_tokens'  => 600,
            'temperature' => 0.3,
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
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw      = curl_exec($ch);
        $curlErr  = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr !== '' || $httpCode < 200 || $httpCode >= 300 || !$raw) {
            $errMsg = $httpCode === 429
                ? 'AI rate limit reached. Please wait a moment or save your profile fields manually below.'
                : 'AI service is temporarily unavailable (' . ($curlErr ?: "HTTP {$httpCode}") . '). You can edit and save your profile manually below.';
            return [
                'reply'     => null,
                'extracted' => [],
                'completed' => false,
                'api_error' => $errMsg,
            ];
        }

        $decoded = json_decode((string) $raw, true);
        $content = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));

        // Strip accidental markdown fences if model emits them
        $content = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $content);
        $parsed  = json_decode(trim((string) $content), true);

        if (!is_array($parsed)) {
            return [
                'reply'     => $content !== '' ? $content : 'Thank you. Please share your target role, key technical skills, and preferred work mode.',
                'extracted' => [],
                'completed' => false,
                'api_error' => null,
            ];
        }

        return [
            'reply'     => (string) ($parsed['follow_up_question'] ?? 'Thank you. Could you share any additional skills or location preferences?'),
            'extracted' => is_array($parsed['extracted'] ?? null) ? $parsed['extracted'] : [],
            'completed' => (bool) ($parsed['is_complete'] ?? false),
            'api_error' => null,
        ];
    }
}
