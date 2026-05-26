<?php

namespace Application\Controller;

use Application\Service\Logger;
use Application\Service\CommonService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * Admin-only log viewer. Lists log files under var/log/ and tails the
 * selected file from the bottom, with an optional case-insensitive
 * substring filter. Limits per-request work (bytes scanned, lines
 * returned) so a runaway tail on a multi-GB log can't hang the request.
 *
 * Access is gated by the standard ACL (resource =
 * Application\Controller\LogViewerController, privilege = index) seeded
 * in the 1.1.2 migration. Only superadmin (role_id 1) gets the privilege
 * by default; other roles must be granted via Manage Roles.
 */
class LogViewerController extends AbstractActionController
{
    private const int MAX_LINES = 5000;
    private const int DEFAULT_LINES = 500;
    private const int READ_CHUNK = 8192;
    private const int MAX_SCAN_BYTES = 16 * 1024 * 1024; // 16 MB

    private string $logDir;

    public function __construct(Logger $logger)
    {
        $this->logDir = $logger->getLogDir();
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $files = $this->listFiles();

        if ($request->isPost() || $this->params()->fromQuery('format') === 'json') {
            $file = (string) ($request->isPost()
                ? $request->getPost('file', '')
                : $this->params()->fromQuery('file', ''));
            $lines = (int) ($request->isPost()
                ? $request->getPost('lines', self::DEFAULT_LINES)
                : $this->params()->fromQuery('lines', self::DEFAULT_LINES));
            $search = trim((string) ($request->isPost()
                ? $request->getPost('q', '')
                : $this->params()->fromQuery('q', '')));

            $payload = $this->tailPayload($files, $file, $lines, $search);

            $response = $this->getResponse();
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
            $response->setContent(CommonService::jsonEncode($payload));
            return $response;
        }

        return new ViewModel([
            'files' => $files,
            'logDir' => $this->logDir,
            'defaultLines' => self::DEFAULT_LINES,
            'maxLines' => self::MAX_LINES,
        ]);
    }

    /**
     * @return array<int, array{name: string, channel: string, size: int, modified: int}>
     */
    private function listFiles(): array
    {
        if (!is_dir($this->logDir)) {
            return [];
        }

        $files = [];
        $entries = @scandir($this->logDir, SCANDIR_SORT_NONE) ?: [];
        foreach ($entries as $name) {
            if ($name === '.' || $name === '..' || !preg_match('/\.log(\.\d+)?$/', $name)) {
                continue;
            }
            $full = $this->logDir . '/' . $name;
            if (!is_file($full)) {
                continue;
            }
            $files[] = [
                'name'     => $name,
                'channel'  => $this->extractChannel($name),
                'size'     => filesize($full) ?: 0,
                'modified' => filemtime($full) ?: 0,
            ];
        }

        usort($files, fn ($a, $b) => $b['modified'] <=> $a['modified']);
        return $files;
    }

    private function extractChannel(string $name): string
    {
        // `app-2026-05-26.log` → `app`; `client-errors-2026-05-26.log` → `client-errors`.
        return (string) preg_replace('/-\d{4}-\d{2}-\d{2}\.log$/', '', $name);
    }

    private function tailPayload(array $files, string $name, int $lines, string $search): array
    {
        $known = array_column($files, 'name');
        if ($name === '' || !in_array($name, $known, true)) {
            return ['error' => 'Unknown log file'];
        }

        $lines = min(self::MAX_LINES, max(10, $lines));
        $path = $this->logDir . '/' . $name;

        if (!is_readable($path)) {
            return ['error' => 'Log file is not readable'];
        }

        [$tail, $scanned] = $this->readTail($path, $lines, $search);
        return [
            'file' => [
                'name'     => $name,
                'channel'  => $this->extractChannel($name),
                'size'     => filesize($path) ?: 0,
                'modified' => filemtime($path) ?: 0,
            ],
            'lines'   => $tail,
            'count'   => count($tail),
            'scanned' => $scanned,
            'search'  => $search,
        ];
    }

    /**
     * Read up to $lines from the end of $path. Walks backwards in 8 KiB
     * chunks; stops at MAX_SCAN_BYTES so a search-with-no-match doesn't
     * read an entire multi-GB log.
     *
     * @return array{0: string[], 1: int} [lines, scanned_byte_count]
     */
    private function readTail(string $path, int $lines, string $search): array
    {
        $handle = @fopen($path, 'rb');
        if ($handle === false) {
            return [[], 0];
        }

        try {
            fseek($handle, 0, SEEK_END);
            $position = ftell($handle);
            $totalSize = $position;
            $scanned = 0;
            $buffer = '';
            $found = [];

            $hasSearch = $search !== '';
            $needle = $hasSearch ? strtolower($search) : '';

            while ($position > 0 && count($found) < $lines && $scanned < self::MAX_SCAN_BYTES) {
                $readSize = (int) min(self::READ_CHUNK, $position);
                $position -= $readSize;
                fseek($handle, $position);
                $chunk = (string) fread($handle, $readSize);
                $buffer = $chunk . $buffer;
                $scanned += $readSize;

                $newlinePos = strpos($buffer, "\n");
                if ($newlinePos === false && $position > 0) {
                    continue;
                }

                $segments = explode("\n", $buffer);
                $buffer = $position > 0 ? array_shift($segments) : '';

                // Walk segments back-to-front so newest lines land in $found first.
                for ($i = count($segments) - 1; $i >= 0; $i--) {
                    $line = $segments[$i];
                    if ($line === '') {
                        continue;
                    }
                    if ($hasSearch && !str_contains(strtolower($line), $needle)) {
                        continue;
                    }
                    $found[] = $line;
                    if (count($found) >= $lines) {
                        break;
                    }
                }
            }

            // Reverse so callers get oldest-first within the tail window.
            return [array_reverse($found), $scanned];
        } finally {
            fclose($handle);
        }
    }
}
