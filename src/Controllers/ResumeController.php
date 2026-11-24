<?php
/**
 * HirePilot AI — Resume Controller
 * Author: Olied Ahmed Chowdhury (ID: 242-134-017, Batch: 7th)
 */

declare(strict_types=1);

namespace HirePilot\Controllers;

use HirePilot\Core\Auth;
use HirePilot\Core\Csrf;
use HirePilot\Core\Flash;
use HirePilot\Models\Resume;
use ZipArchive;

class ResumeController
{
    private Resume $resumeModel;

    public function __construct()
    {
        Auth::require();
        $this->resumeModel = new Resume();
    }

    // ── GET /resume ───────────────────────────────────────────────────────────
    public function index(array $params = []): void
    {
        $userId  = Auth::id();
        $resumes = $this->resumeModel->getAllByUser($userId);

        $pageTitle = 'My Resumes';
        $navbarTitle = 'Resume Management';

        ob_start();
        require VIEWS_PATH . '/resume/index.php';
        $content = ob_get_clean();
        require VIEWS_PATH . '/layouts/app.php';
    }

    // ── POST /resume/upload ───────────────────────────────────────────────────
    public function upload(array $params = []): void
    {
        try {
            Csrf::verify();
            $userId = Auth::id();

            if (!isset($_FILES['resume_file']) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK) {
                throw new \InvalidArgumentException('Please select a valid file to upload.');
            }

            $file     = $_FILES['resume_file'];
            $maxBytes = (int) config('upload.max_size', 5242880); // 5 MB default
            if ($file['size'] > $maxBytes) {
                throw new \InvalidArgumentException('File exceeds the 5MB size limit.');
            }

            $origName = basename($file['name']);
            $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            $allowedExts = ['pdf', 'doc', 'docx'];
            if (!in_array($ext, $allowedExts, true)) {
                throw new \InvalidArgumentException('Only PDF, DOC, and DOCX files are allowed.');
            }

            // Verify MIME type using finfo
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']) ?: 'application/octet-stream';

            $allowedMimes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/zip', // Some DOCX files are identified as application/zip
            ];

            if (!in_array($mimeType, $allowedMimes, true)) {
                throw new \InvalidArgumentException('Invalid file format detected.');
            }

            // Generate safe filename
            $safeName   = bin2hex(random_bytes(16)) . '.' . $ext;
            $uploadDir  = STORAGE_PATH . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $targetPath = $uploadDir . '/' . $safeName;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \RuntimeException('Failed to save uploaded file to storage.');
            }

            // Extract plain text for AI drafting
            $extractedText = $this->extractTextFromFile($targetPath, $ext);

            $label = trim($_POST['label'] ?? '');
            if (empty($label)) {
                $label = pathinfo($origName, PATHINFO_FILENAME);
            }

            $isPrimary = !empty($_POST['is_primary']) || count($this->resumeModel->getAllByUser($userId)) === 0;

            $this->resumeModel->create([
                'user_id'       => $userId,
                'filename'      => $safeName,
                'original_name' => $origName,
                'mime_type'     => $mimeType,
                'file_size'     => $file['size'],
                'file_path'     => 'storage/uploads/' . $safeName,
                'is_primary'    => $isPrimary ? 1 : 0,
                'label'         => $label,
                'parsed_text'   => $extractedText,
            ]);

            Flash::success("Resume \"{$label}\" uploaded successfully!");
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/resume');
    }

    // ── GET /resume/{id}/download ─────────────────────────────────────────────
    public function download(array $params = []): void
    {
        $id     = (int)($params['id'] ?? 0);
        $userId = Auth::id();
        $resume = $this->resumeModel->findById($id, $userId);

        if (!$resume) {
            Flash::error('Resume not found.');
            Auth::redirect('/resume');
        }

        $fullPath = STORAGE_PATH . '/uploads/' . $resume['filename'];
        if (!file_exists($fullPath)) {
            Flash::error('File does not exist on disk.');
            Auth::redirect('/resume');
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $resume['mime_type']);
        header('Content-Disposition: attachment; filename="' . addslashes($resume['original_name']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }

    // ── POST /resume/{id}/primary ─────────────────────────────────────────────
    public function setPrimary(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            $this->resumeModel->setPrimary($id, $userId);
            Flash::success('Default primary resume updated.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/resume');
    }

    // ── POST /resume/{id}/delete ──────────────────────────────────────────────
    public function delete(array $params = []): void
    {
        try {
            Csrf::verify();
            $id     = (int)($params['id'] ?? 0);
            $userId = Auth::id();

            $this->resumeModel->delete($id, $userId);
            Flash::success('Resume deleted successfully.');
        } catch (\Throwable $e) {
            Flash::error($e->getMessage());
        }

        Auth::redirect('/resume');
    }

    /**
     * Text extractor helper for PDF and DOCX documents.
     */
    private function extractTextFromFile(string $filePath, string $ext): string
    {
        $text = '';

        if ($ext === 'docx') {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === true) {
                if (($xmlIndex = $zip->locateName('word/document.xml')) !== false) {
                    $xmlData = $zip->getFromIndex($xmlIndex);
                    $text = strip_tags($xmlData);
                }
                $zip->close();
            }
        } elseif ($ext === 'pdf') {
            // Read raw stream content for text blocks
            $content = @file_get_contents($filePath);
            if ($content !== false) {
                // Basic extraction of text objects between BT and ET tags
                if (preg_match_all('/\((.*?)\)\s*T[jJ]/', $content, $matches)) {
                    $text = implode(' ', $matches[1]);
                } else {
                    // Fallback to strip binary characters
                    $text = preg_replace('/[^\x20-\x7E\t\r\n]/', ' ', substr($content, 0, 10000));
                }
            }
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
