<?php
/**
 * HirePilot AI — Google OAuth Service
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 *
 * Uses Google's OAuth2 endpoints directly (no SDK needed).
 * Requires GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET, GOOGLE_REDIRECT_URI in .env
 */

declare(strict_types=1);

namespace HirePilot\Services;

class GoogleAuthService
{
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USER_URL  = 'https://www.googleapis.com/oauth2/v3/userinfo';
    private const SCOPES    = 'openid email profile';

    public function __construct()
    {
        $this->clientId     = (string) config('google.client_id');
        $this->clientSecret = (string) config('google.client_secret');
        $this->redirectUri  = (string) config('google.redirect_uri');
    }

    /**
     * Check if Google OAuth is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret) && !empty($this->redirectUri);
    }

    /**
     * Generate the Google OAuth authorization URL.
     */
    public function getAuthUrl(): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Store a random state token in session for CSRF protection
        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        $params = http_build_query([
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->redirectUri,
            'response_type' => 'code',
            'scope'         => self::SCOPES,
            'access_type'   => 'offline',
            'prompt'        => 'select_account',
            'state'         => $state,
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Exchange the authorization code for user info.
     * Returns array with: id, name, email, picture, token
     */
    public function authenticate(string $code): ?array
    {
        // 1. Verify state
        $state = $_GET['state'] ?? '';
        if ($state !== ($_SESSION['google_oauth_state'] ?? '')) {
            throw new \RuntimeException('Invalid OAuth state. Possible CSRF attack.');
        }
        unset($_SESSION['google_oauth_state']);

        // 2. Exchange code for tokens
        $tokens = $this->exchangeCode($code);
        if (!$tokens || empty($tokens['access_token'])) {
            return null;
        }

        // 3. Fetch user info
        $userInfo = $this->fetchUserInfo($tokens['access_token']);
        if (!$userInfo || empty($userInfo['email'])) {
            return null;
        }

        return [
            'id'      => $userInfo['sub'],
            'name'    => $userInfo['name'] ?? 'Google User',
            'email'   => strtolower($userInfo['email']),
            'picture' => $userInfo['picture'] ?? null,
            'token'   => $tokens['access_token'],
        ];
    }

    /**
     * POST to Google's token endpoint to exchange code for access_token.
     */
    private function exchangeCode(string $code): ?array
    {
        $payload = http_build_query([
            'code'          => $code,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code',
        ]);

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n"
                           . "Accept: application/json\r\n",
                'content' => $payload,
                'timeout' => 10,
            ],
            'ssl' => ['verify_peer' => true],
        ]);

        $response = @file_get_contents(self::TOKEN_URL, false, $ctx);
        if ($response === false) {
            return null;
        }

        return json_decode($response, true) ?: null;
    }

    /**
     * GET user info from Google using the access token.
     */
    private function fetchUserInfo(string $accessToken): ?array
    {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "Authorization: Bearer {$accessToken}\r\n"
                           . "Accept: application/json\r\n",
                'timeout' => 10,
            ],
            'ssl' => ['verify_peer' => true],
        ]);

        $response = @file_get_contents(self::USER_URL, false, $ctx);
        if ($response === false) {
            return null;
        }

        return json_decode($response, true) ?: null;
    }
}

