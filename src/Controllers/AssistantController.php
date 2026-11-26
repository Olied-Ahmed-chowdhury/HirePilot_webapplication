<?php
/**
 * HirePilot AI — Floating Website Guide Chatbot Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Answers user questions about how to use HirePilot AI in simple, friendly steps.
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Models\User;

class AssistantController
{
    public function __construct()
    {
        Auth::require();
    }

    // ── POST /assistant/chat ──────────────────────────────────────────────────
    public function chat(array $params = []): void
    {
        header('Content-Type: application/json');

        if (!Csrf::validate()) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'Session expired. Please refresh the page.']);
            return;
        }

        $input = json_decode((string) file_get_contents('php://input'), true) ?: $_POST;
        $message = trim((string) ($input['message'] ?? ''));
        $history = is_array($input['history'] ?? null) ? $input['history'] : [];

        if ($message === '') {
            http_response_code(422);
            echo json_encode(['ok' => false, 'error' => 'Please type a question first.']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findById(Auth::id()) ?? [];

        $apiKey = (string) config('ai.api_key', '');
        $apiUrl = (string) config('ai.api_url', 'https://api.groq.com/openai/v1/chat/completions');
        $model  = (string) config('ai.model', 'llama-3.3-70b-versatile');

        if ($apiKey === '') {
            echo json_encode([
                'ok'    => true,
                'reply' => $this->localGuideFallback($message),
            ]);
            return;
        }

        $userName = $user['name'] ?? 'there';
        $targetRole = $user['target_role'] ?? 'not set yet';

        $systemPrompt = <<<PROMPT
You are the friendly HirePilot AI Website Assistant. Your job is to explain how to use the HirePilot AI website in super simple, clear, step-by-step plain English.
Current user: {$userName} (Target role: {$targetRole}).

Website sections you can guide the user to:
1. Dashboard (/dashboard) — Overview of match scores, active applications, and quick actions.
2. Job Discovery (/jobs) — Browse live jobs synced from Fantastic.jobs (8M+ ATS/remote jobs) and LinkedIn (via Apify). Click "Sync Live Jobs" for fresh postings or "Match My Resume (LinkedIn)" to find jobs matching your uploaded resume & skills.
3. Resume Manager (/resume) — Upload your PDF/DOCX resume so HirePilot can match you to relevant jobs and draft better cover letters.
4. AI Onboarding & Profile (/onboarding, /profile) — Chat with AI or fill in your target role, skills, and work preference (Remote/Hybrid/Onsite).
5. Email Drafts (/emails, /emails/create) — Select any job and click "Draft AI" to generate a tailored application email & cover letter, review/edit it, and approve before sending.
6. Applications Tracker (/applications) — Track every job you applied to (Saved, Applied, Interview, Offer, Rejected) with instant status dropdowns.

Keep answers short (2–5 bullet points or sentences), clear, encouraging, and beginner-friendly.
PROMPT;

        $messages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach (array_slice($history, -6) as $turn) {
            if (!empty($turn['content'])) {
                $messages[] = [
                    'role'    => ($turn['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                    'content' => (string) $turn['content'],
                ];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => 450,
                'temperature' => 0.4,
            ]),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err === '' && $code >= 200 && $code < 300 && $raw) {
            $data  = json_decode($raw, true);
            $reply = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
            if ($reply !== '') {
                echo json_encode(['ok' => true, 'reply' => $reply]);
                return;
            }
        }

        echo json_encode([
            'ok'    => true,
            'reply' => $this->localGuideFallback($message),
        ]);
    }

    private function localGuideFallback(string $message): string
    {
        $q = strtolower($message);
        if (str_contains($q, 'resume') || str_contains($q, 'cv') || str_contains($q, 'linkedin')) {
            return "To find jobs matching your resume:\n1. Go to **Resume** in the sidebar and upload your PDF or DOCX resume.\n2. Open **Explore Jobs** and click **Match My Resume (LinkedIn)** — HirePilot searches LinkedIn (Apify) and live ATS feeds (Fantastic.jobs) using your skills and target role.";
        }
        if (str_contains($q, 'email') || str_contains($q, 'cover') || str_contains($q, 'draft')) {
            return "To create an AI application email:\n1. Open **Explore Jobs** and click **Draft AI** on any job card.\n2. HirePilot uses your profile and resume to write a tailored subject and message.\n3. Review, edit if needed, and click **Approve** before sending.";
        }
        if (str_contains($q, 'track') || str_contains($q, 'application') || str_contains($q, 'status')) {
            return "In **Applications**, you can see every job you saved or applied to. Use the **Status** dropdown on any row to switch between Saved, Applied, Interview, Offer, or Rejected — it saves automatically!";
        }
        return "Here is how to get started on HirePilot AI:\n1. **Profile / Onboarding**: Set your target role and skills.\n2. **Resume**: Upload your CV.\n3. **Explore Jobs**: Click **Sync Live Jobs** or **Match My Resume** to pull real openings.\n4. **Draft AI & Applications**: Generate tailored cover letters and track your progress!";
    }
}
