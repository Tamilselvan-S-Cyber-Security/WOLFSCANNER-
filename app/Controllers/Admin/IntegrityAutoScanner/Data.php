<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\IntegrityAutoScanner;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    private const DEFAULT_TIMEOUT_SECONDS = 8;
    private const MAX_FILE_BYTES = 20000000;
    private const MAX_ZIP_ENTRY_BYTES = 500000;
    private const MAX_EXPORT_FINDINGS = 1000;
    private const SCHEDULE_CONFIG_FILE = 'tmp/integrity_auto_scanner_schedule.json';
    private const SCHEDULE_STATE_FILE = 'tmp/integrity_auto_scanner_state.json';
    private const ALERT_LOG_FILE = 'assets/logs/integrity_alerts.log';
    private const MIN_SCHEDULE_MINUTES = 1;
    private const MAX_SCHEDULE_MINUTES = 1440;

    public function proceedPostRequest(): array {
        return $this->scan();
    }

    private function scan(): array {
        $params = $this->extractRequestParams([
            'token',
            'scanType',
            'url_target',
            'allow_private',
            'auto_fix',
            'enable_alerts',
            'schedule_enabled',
            'schedule_minutes',
            'findings_search',
            'findings_severity',
            'findings_sort',
        ]);

        $pageParams = [
            'SCAN_VALUES' => $params,
        ];

        try {
            $errorCode = $this->validateCsrfToken();
            if ($errorCode) {
                $pageParams['ERROR_CODE'] = $errorCode;
                return $pageParams;
            }

            $scanType = (string)($params['scanType'] ?? '');
            if (!in_array($scanType, ['file', 'zip', 'url'], true)) {
                $pageParams['ERROR_MESSAGE'] = 'Select a scan type.';
                return $pageParams;
            }

            if ($scanType === 'file') {
                $scanResponse = $this->scanFile();
            } elseif ($scanType === 'zip') {
                $scanResponse = $this->scanZip();
            } else {
                $scanResponse = $this->scanUrl($params);
            }

            if (!isset($scanResponse['SCAN_RESULT']) || !is_array($scanResponse['SCAN_RESULT'])) {
                return array_merge($pageParams, $scanResponse);
            }

            $scanResult = $scanResponse['SCAN_RESULT'];
            $scanResult = $this->finalizeResult($scanResult, $params);
            $scanResponse['SCAN_RESULT'] = $scanResult;

            if ($this->toBool($params['schedule_enabled'] ?? null)) {
                $scheduleMinutes = (int)($params['schedule_minutes'] ?? 60);
                $scheduleMinutes = max(self::MIN_SCHEDULE_MINUTES, min(self::MAX_SCHEDULE_MINUTES, $scheduleMinutes));
                $this->saveScheduleConfig([
                    'enabled' => true,
                    'interval_minutes' => $scheduleMinutes,
                    'enable_alerts' => $this->toBool($params['enable_alerts'] ?? null),
                    'updated_at' => gmdate('c'),
                ]);
                $scanResponse['SCHEDULE_MESSAGE'] = sprintf('Scheduled scan saved (%d minute interval).', $scheduleMinutes);
            } elseif (isset($params['schedule_enabled'])) {
                $this->saveScheduleConfig([
                    'enabled' => false,
                    'interval_minutes' => 60,
                    'enable_alerts' => false,
                    'updated_at' => gmdate('c'),
                ]);
                $scanResponse['SCHEDULE_MESSAGE'] = 'Scheduled scan disabled.';
            }

            return array_merge($pageParams, $scanResponse);
        } catch (\Throwable $e) {
            $pageParams['ERROR_MESSAGE'] = 'Scan failed: ' . $e->getMessage();
            return $pageParams;
        }
    }

    private function scanFile(): array {
        if (!isset($_FILES['scan_file'])) {
            return ['ERROR_MESSAGE' => 'File is required.'];
        }

        $file = $_FILES['scan_file'];
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ERROR_MESSAGE' => 'File upload failed.'];
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        if ($tmp === '' || !is_file($tmp)) {
            return ['ERROR_MESSAGE' => 'Uploaded file is missing.'];
        }

        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            return ['ERROR_MESSAGE' => 'File size is invalid or too large.'];
        }

        $name = (string)($file['name'] ?? 'uploaded');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $sha256 = hash_file('sha256', $tmp) ?: null;
        $md5 = hash_file('md5', $tmp) ?: null;

        $mime = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $tmp) ?: null;
                finfo_close($finfo);
            }
        }

        $findings = [];
        if (in_array($ext, ['php', 'phtml', 'phar', 'exe', 'dll', 'bat', 'cmd', 'ps1', 'vbs'], true)) {
            $findings[] = [
                'severity' => 'high',
                'message' => 'Uploaded file has a potentially dangerous executable/script extension.',
                'rule' => 'dangerous_extension',
                'file' => $name,
            ];
        }
        if (is_string($mime) && preg_match('/php|x-msdownload|x-dosexec|x-executable/i', $mime)) {
            $findings[] = [
                'severity' => 'high',
                'message' => 'Uploaded file MIME type is executable/script-like.',
                'rule' => 'dangerous_mime',
                'file' => $name,
            ];
        }

        return [
            'SCAN_RESULT' => [
                'type' => 'file_integrity',
                'filename' => $name,
                'size' => $size,
                'mime' => $mime,
                'hashes' => [
                    'sha256' => $sha256,
                    'md5' => $md5,
                ],
                'findings' => $findings,
                'malware' => $this->buildMalwareReputationPayload(is_string($sha256) ? $sha256 : null),
            ],
        ];
    }

    private function scanZip(): array {
        if (!isset($_FILES['scan_zip'])) {
            return ['ERROR_MESSAGE' => 'ZIP file is required.'];
        }

        $file = $_FILES['scan_zip'];
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ERROR_MESSAGE' => 'ZIP upload failed.'];
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        if ($tmp === '' || !is_file($tmp)) {
            return ['ERROR_MESSAGE' => 'Uploaded ZIP is missing.'];
        }

        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            return ['ERROR_MESSAGE' => 'ZIP size is invalid or too large.'];
        }

        if (!class_exists('ZipArchive')) {
            return ['ERROR_MESSAGE' => 'ZipArchive is not available on this server.'];
        }

        $zip = new \ZipArchive();
        $open = $zip->open($tmp);
        if ($open !== true) {
            return ['ERROR_MESSAGE' => 'Failed to open ZIP.'];
        }

        $rules = $this->getStaticRules();
        $findings = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!$stat || !isset($stat['name'])) {
                continue;
            }

            $entryName = (string)$stat['name'];
            $entrySize = (int)($stat['size'] ?? 0);
            $entryExt = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));

            if (
                str_contains($entryName, '../') ||
                str_contains($entryName, '..\\') ||
                str_starts_with($entryName, '/') ||
                preg_match('/^[a-z]:[\\\\\\/]/i', $entryName)
            ) {
                $findings[] = [
                    'file' => $entryName,
                    'rule' => 'zip_path_traversal',
                    'severity' => 'critical',
                    'message' => 'ZIP entry path indicates traversal or absolute path.',
                ];
            }

            if (in_array($entryExt, ['php', 'phtml', 'phar', 'exe', 'dll', 'bat', 'cmd', 'ps1'], true)) {
                $findings[] = [
                    'file' => $entryName,
                    'rule' => 'zip_dangerous_extension',
                    'severity' => 'high',
                    'message' => 'ZIP contains potentially dangerous executable/script file.',
                ];
            }

            if ($entrySize <= 0) {
                continue;
            }

            if ($entrySize > self::MAX_ZIP_ENTRY_BYTES) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if (!is_string($content)) {
                continue;
            }

            foreach ($rules as $rule) {
                if (!$this->ruleAppliesToFile($rule, $entryName)) {
                    continue;
                }

                if (@preg_match($rule['pattern'], $content) === 1) {
                    $findings[] = [
                        'file' => $entryName,
                        'rule' => $rule['id'],
                        'severity' => $rule['severity'],
                        'message' => $rule['message'],
                    ];
                }
            }
        }

        $zip->close();

        $name = (string)($file['name'] ?? 'archive.zip');

        $zipSha = hash_file('sha256', $tmp) ?: null;

        return [
            'SCAN_RESULT' => [
                'type' => 'static_zip_scan',
                'filename' => $name,
                'size' => $size,
                'hashes' => [
                    'sha256' => $zipSha,
                    'md5' => hash_file('md5', $tmp) ?: null,
                ],
                'findings' => $findings,
                'malware' => $this->buildMalwareReputationPayload(is_string($zipSha) ? $zipSha : null),
            ],
        ];
    }

    private function buildMalwareReputationPayload(?string $sha256): array {
        $apiKey = \Wolf\Utils\Variables::getCloudFileReputationApiKey();
        if (!$apiKey) {
            return [
                'enabled' => false,
                'queried' => false,
                'status' => 'skipped',
                'message' => 'Cloud reputation lookup is not configured. Set CLOUD_FILE_REPUTATION_API_KEY in local configuration.',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        if (!$sha256 || strlen($sha256) !== 64 || !ctype_xdigit($sha256)) {
            return [
                'enabled' => true,
                'queried' => false,
                'status' => 'skipped',
                'message' => 'SHA-256 hash missing; cloud reputation was skipped.',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        return $this->queryCloudFileReputationApi($sha256, $apiKey);
    }

    private function queryCloudFileReputationApi(string $sha256, string $apiKey): array {
        if (!function_exists('curl_init')) {
            return [
                'enabled' => true,
                'queried' => true,
                'status' => 'error',
                'message' => 'cURL is required for cloud reputation lookup.',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        $url = 'https://www.virustotal.com/api/v3/files/' . $sha256;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'x-apikey: ' . $apiKey,
            'Accept: application/json',
        ]);

        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false) {
            return [
                'enabled' => true,
                'queried' => true,
                'status' => 'error',
                'message' => 'Cloud reputation request failed (network).',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        if ($code === 404) {
            return [
                'enabled' => true,
                'queried' => true,
                'status' => 'not_found',
                'message' => 'No reputation data for this hash yet.',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        if ($code === 429) {
            return [
                'enabled' => true,
                'queried' => true,
                'status' => 'error',
                'message' => 'Cloud reputation rate limit reached. Try again later.',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        if ($code < 200 || $code >= 300) {
            return [
                'enabled' => true,
                'queried' => true,
                'status' => 'error',
                'message' => 'Cloud reputation request failed (HTTP ' . $code . '). Verify your API key.',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        $json = json_decode($body, true);
        $statsRaw = is_array($json) ? ($json['data']['attributes']['last_analysis_stats'] ?? null) : null;
        if (!is_array($statsRaw)) {
            return [
                'enabled' => true,
                'queried' => true,
                'status' => 'error',
                'message' => 'Unexpected cloud reputation response.',
                'stats' => null,
                'reputation_level' => 'unknown',
            ];
        }

        $mal = (int)($statsRaw['malicious'] ?? 0);
        $sus = (int)($statsRaw['suspicious'] ?? 0);
        $harm = (int)($statsRaw['harmless'] ?? 0);
        $und = (int)($statsRaw['undetected'] ?? 0);
        $timeout = (int)($statsRaw['timeout'] ?? 0);
        $failure = (int)($statsRaw['failure'] ?? 0);

        $level = 'unknown';
        if ($mal > 0) {
            $level = 'malicious';
        } elseif ($sus > 0) {
            $level = 'suspicious';
        } elseif ($harm + $und > 0) {
            $level = 'clean';
        }

        $stats = [
            'malicious' => $mal,
            'suspicious' => $sus,
            'harmless' => $harm,
            'undetected' => $und,
            'timeout' => $timeout,
            'failure' => $failure,
        ];

        return [
            'enabled' => true,
            'queried' => true,
            'status' => 'ok',
            'message' => 'Aggregated engine results for this file hash.',
            'stats' => $stats,
            'reputation_level' => $level,
        ];
    }

    private function scanUrl(array $params): array {
        $targetRaw = trim((string)($params['url_target'] ?? ''));
        if ($targetRaw === '') {
            return ['ERROR_MESSAGE' => 'URL is required.'];
        }

        $allowPrivate = ($params['allow_private'] ?? null) ? true : false;

        $parsed = $this->parseUrlTarget($targetRaw);
        if (isset($parsed['error'])) {
            return ['ERROR_MESSAGE' => $parsed['error']];
        }

        $url = $parsed['normalized'];
        $host = $parsed['host'];

        if (!$allowPrivate) {
            $blockedReason = $this->getBlockedReason($host);
            if ($blockedReason !== null) {
                return ['ERROR_MESSAGE' => $blockedReason];
            }
        }

        $http = $this->fetchHeaders($url);
        $findings = $this->analyzeSecurityHeaders($http['headers'] ?? '');

        return [
            'SCAN_RESULT' => [
                'type' => 'url_scan',
                'target' => $url,
                'host' => $host,
                'http' => $http,
                'findings' => $findings,
            ],
        ];
    }

    private function parseUrlTarget(string $targetRaw): array {
        $withScheme = $targetRaw;
        if (!preg_match('~^https?://~i', $withScheme) && preg_match('~^[a-z0-9.-]+(?::\d+)?(?:/.*)?$~i', $withScheme)) {
            $withScheme = 'http://' . $withScheme;
        }

        $parts = @parse_url($withScheme);
        if (!$parts || !isset($parts['host'])) {
            return ['error' => 'Invalid URL.'];
        }

        $scheme = strtolower((string)($parts['scheme'] ?? 'http'));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return ['error' => 'Only http/https URLs are allowed.'];
        }

        $host = (string)$parts['host'];
        $path = (string)($parts['path'] ?? '');
        $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
        $port = isset($parts['port']) ? (':' . $parts['port']) : '';

        return [
            'normalized' => $scheme . '://' . $host . $port . $path . $query,
            'host' => $host,
        ];
    }

    private function getBlockedReason(string $host): ?string {
        $hostLower = strtolower($host);
        if ($hostLower === 'localhost' || $this->endsWith($hostLower, '.localhost')) {
            return 'Blocked target: localhost is not allowed.';
        }

        $resolved = @gethostbyname($host);
        if ($resolved && filter_var($resolved, FILTER_VALIDATE_IP) && $this->isPrivateOrReservedIp($resolved)) {
            return 'Blocked target: host resolves to private/reserved IP.';
        }

        return null;
    }

    private function endsWith(string $haystack, string $needle): bool {
        $len = strlen($needle);
        if ($len === 0) {
            return true;
        }

        return substr($haystack, -$len) === $needle;
    }

    private function isPrivateOrReservedIp(string $ip): bool {
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) === false;
    }

    private function fetchHeaders(string $url): array {
        $response = [
            'status' => null,
            'headers' => null,
            'error' => null,
        ];

        if (!function_exists('curl_init')) {
            $response['error'] = 'cURL is not available.';
            return $response;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_TIMEOUT_SECONDS);
        curl_setopt($ch, CURLOPT_USERAGENT, 'WolfScannerIntegrityAutoScanner/1.0');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $response['error'] = curl_error($ch);
            curl_close($ch);
            return $response;
        }

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response['status'] = $status;
        $response['headers'] = $raw;

        return $response;
    }

    private function analyzeSecurityHeaders(?string $headersRaw): array {
        $headersRaw = $headersRaw ?? '';
        $h = strtolower($headersRaw);

        $findings = [];

        if (!str_contains($h, 'content-security-policy:')) {
            $findings[] = ['severity' => 'medium', 'message' => 'Missing Content-Security-Policy (CSP) header.'];
        }

        if (!str_contains($h, 'strict-transport-security:')) {
            $findings[] = ['severity' => 'low', 'message' => 'Missing Strict-Transport-Security (HSTS) header.'];
        }

        if (!str_contains($h, 'x-frame-options:') && !str_contains($h, 'frame-ancestors')) {
            $findings[] = ['severity' => 'low', 'message' => 'Missing clickjacking protection (X-Frame-Options or CSP frame-ancestors).'];
        }

        if (!str_contains($h, 'x-content-type-options: nosniff')) {
            $findings[] = ['severity' => 'low', 'message' => 'Missing X-Content-Type-Options: nosniff header.'];
        }

        if (!str_contains($h, 'referrer-policy:')) {
            $findings[] = ['severity' => 'low', 'message' => 'Missing Referrer-Policy header.'];
        }

        if (!str_contains($h, 'permissions-policy:')) {
            $findings[] = ['severity' => 'low', 'message' => 'Missing Permissions-Policy header.'];
        }

        return $findings;
    }

    private function ruleAppliesToFile(array $rule, string $fileName): bool {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($ext, $rule['extensions'], true);
    }

    private function getStaticRules(): array {
        return [
            [
                'id' => 'php_eval',
                'severity' => 'high',
                'message' => 'Potential code execution: eval() detected.',
                'pattern' => '/\beval\s*\(/i',
                'extensions' => ['php', 'phtml'],
            ],
            [
                'id' => 'php_system',
                'severity' => 'high',
                'message' => 'Potential command execution: system/exec/shell_exec detected.',
                'pattern' => '/\b(system|exec|shell_exec|passthru|proc_open)\s*\(/i',
                'extensions' => ['php', 'phtml'],
            ],
            [
                'id' => 'php_base64_decode',
                'severity' => 'medium',
                'message' => 'Obfuscation indicator: base64_decode detected.',
                'pattern' => '/\bbase64_decode\s*\(/i',
                'extensions' => ['php', 'phtml'],
            ],
            [
                'id' => 'php_gzinflate',
                'severity' => 'medium',
                'message' => 'Obfuscation indicator: gzinflate/gzuncompress detected.',
                'pattern' => '/\b(gzinflate|gzuncompress)\s*\(/i',
                'extensions' => ['php', 'phtml'],
            ],
            [
                'id' => 'php_preg_replace_eval',
                'severity' => 'high',
                'message' => 'Potential code execution: preg_replace /e modifier detected.',
                'pattern' => '/preg_replace\s*\(\s*[\'"][^\'"]*\/e[\'"]/i',
                'extensions' => ['php', 'phtml'],
            ],
            [
                'id' => 'php_assert_exec',
                'severity' => 'high',
                'message' => 'Potential code execution: assert() with string input pattern.',
                'pattern' => '/\bassert\s*\(\s*[\$\'"]/i',
                'extensions' => ['php', 'phtml'],
            ],
            [
                'id' => 'js_document_write',
                'severity' => 'low',
                'message' => 'JS risky pattern: document.write detected.',
                'pattern' => '/\bdocument\.write\s*\(/i',
                'extensions' => ['js', 'html', 'htm'],
            ],
        ];
    }

    private function finalizeResult(array $scanResult, array $params): array {
        $scanResult['generated_at'] = gmdate('c');

        $findings = isset($scanResult['findings']) && is_array($scanResult['findings']) ? $scanResult['findings'] : [];
        $findings = $this->normalizeFindings($findings);
        $findings = $this->filterAndSortFindings($findings, $params);
        $scanResult['findings'] = $findings;
        $scanResult['summary'] = $this->summarizeFindings($findings);

        $autoFixEnabled = $this->toBool($params['auto_fix'] ?? null);
        $scanResult['auto_fix'] = [
            'enabled' => $autoFixEnabled,
            'applied' => false,
            'actions' => $autoFixEnabled ? $this->buildAutoFixActions($scanResult) : [],
            'message' => $autoFixEnabled
                ? 'Auto-fix suggestions prepared. Review before applying.'
                : 'Auto-fix disabled.',
        ];

        $scanResult['export'] = [
            'json' => $this->buildJsonExport($scanResult),
            'csv' => $this->buildCsvExport($scanResult),
        ];

        if ($this->toBool($params['enable_alerts'] ?? null) && $this->shouldAlert($findings)) {
            $alertMessage = sprintf(
                '[%s] %s findings detected for %s (%d findings).',
                gmdate('c'),
                strtoupper((string)$scanResult['summary']['highest_severity']),
                (string)($scanResult['type'] ?? 'scan'),
                (int)($scanResult['summary']['total'] ?? 0)
            );
            $this->appendAlertLog($alertMessage);
            $scanResult['alert'] = [
                'triggered' => true,
                'message' => $alertMessage,
            ];
        } else {
            $scanResult['alert'] = [
                'triggered' => false,
                'message' => 'No alert triggered.',
            ];
        }

        return $scanResult;
    }

    private function normalizeFindings(array $findings): array {
        $out = [];
        foreach ($findings as $finding) {
            if (!is_array($finding)) {
                continue;
            }
            $out[] = [
                'severity' => strtolower((string)($finding['severity'] ?? 'low')),
                'message' => (string)($finding['message'] ?? ''),
                'file' => isset($finding['file']) ? (string)$finding['file'] : '',
                'rule' => isset($finding['rule']) ? (string)$finding['rule'] : '',
            ];
        }

        return $out;
    }

    private function filterAndSortFindings(array $findings, array $params): array {
        $search = strtolower(trim((string)($params['findings_search'] ?? '')));
        $severity = strtolower(trim((string)($params['findings_severity'] ?? 'all')));
        $sort = strtolower(trim((string)($params['findings_sort'] ?? 'severity_desc')));

        $filtered = [];
        foreach ($findings as $finding) {
            if ($severity !== '' && $severity !== 'all' && ($finding['severity'] ?? '') !== $severity) {
                continue;
            }

            if ($search !== '') {
                $haystack = strtolower(
                    ($finding['message'] ?? '') . ' ' .
                    ($finding['file'] ?? '') . ' ' .
                    ($finding['rule'] ?? '') . ' ' .
                    ($finding['severity'] ?? '')
                );
                if (!str_contains($haystack, $search)) {
                    continue;
                }
            }

            $filtered[] = $finding;
        }

        usort($filtered, function (array $a, array $b) use ($sort): int {
            $priority = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            $ap = $priority[$a['severity'] ?? 'low'] ?? 0;
            $bp = $priority[$b['severity'] ?? 'low'] ?? 0;

            if ($sort === 'severity_asc') {
                return $ap <=> $bp;
            }
            if ($sort === 'message_asc') {
                return strcmp((string)($a['message'] ?? ''), (string)($b['message'] ?? ''));
            }
            if ($sort === 'message_desc') {
                return strcmp((string)($b['message'] ?? ''), (string)($a['message'] ?? ''));
            }

            return $bp <=> $ap;
        });

        return $filtered;
    }

    private function summarizeFindings(array $findings): array {
        $summary = [
            'total' => count($findings),
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'highest_severity' => 'none',
        ];

        foreach ($findings as $finding) {
            $sev = strtolower((string)($finding['severity'] ?? 'low'));
            if (isset($summary[$sev])) {
                $summary[$sev]++;
            }
        }

        foreach (['critical', 'high', 'medium', 'low'] as $sev) {
            if ($summary[$sev] > 0) {
                $summary['highest_severity'] = $sev;
                break;
            }
        }

        return $summary;
    }

    private function buildAutoFixActions(array $scanResult): array {
        $actions = [];
        $type = (string)($scanResult['type'] ?? '');
        foreach (($scanResult['findings'] ?? []) as $finding) {
            $rule = (string)($finding['rule'] ?? '');
            $file = (string)($finding['file'] ?? '');
            if ($rule === 'zip_path_traversal') {
                $actions[] = sprintf('Remove suspicious ZIP entry path: %s', $file);
            } elseif ($rule === 'dangerous_extension' || $rule === 'zip_dangerous_extension') {
                $actions[] = sprintf('Quarantine or block executable/script file: %s', $file);
            } elseif (str_starts_with($rule, 'php_')) {
                $actions[] = sprintf('Manually review and refactor risky PHP pattern (%s) in %s', $rule, $file ?: 'target file');
            } else {
                $actions[] = sprintf('Review finding: %s', (string)($finding['message'] ?? 'Unknown finding'));
            }
        }

        if ($type === 'url_scan') {
            $actions[] = 'Add CSP, HSTS, X-Content-Type-Options, Referrer-Policy, and Permissions-Policy headers.';
        }

        return array_values(array_unique($actions));
    }

    private function buildJsonExport(array $scanResult): string {
        $recommendations = $this->getExportRecommendations($scanResult);
        $exportPayload = $scanResult;
        // Keep this key last for easy reading in downloaded JSON.
        $exportPayload['recommendations_last_section'] = $recommendations;

        $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS;

        return (string)json_encode($exportPayload, $flags);
    }

    private function buildCsvExport(array $scanResult): string {
        $lines = ['severity,rule,file,message'];
        $findings = array_slice((array)($scanResult['findings'] ?? []), 0, self::MAX_EXPORT_FINDINGS);
        foreach ($findings as $finding) {
            $lines[] = $this->csv([
                (string)($finding['severity'] ?? ''),
                (string)($finding['rule'] ?? ''),
                (string)($finding['file'] ?? ''),
                (string)($finding['message'] ?? ''),
            ]);
        }

        $recommendations = $this->getExportRecommendations($scanResult);
        $lines[] = '';
        $lines[] = 'Recommendations';
        $lines[] = 'index,recommendation';
        foreach ($recommendations as $idx => $rec) {
            $lines[] = $this->csv([
                (string)($idx + 1),
                (string)$rec,
            ]);
        }

        $body = implode("\n", $lines);

        return "\xEF\xBB\xBF" . $body;
    }

    private function csv(array $values): string {
        $escaped = array_map(static function (string $v): string {
            return '"' . str_replace('"', '""', $v) . '"';
        }, $values);

        return implode(',', $escaped);
    }

    private function shouldAlert(array $findings): bool {
        foreach ($findings as $finding) {
            $sev = strtolower((string)($finding['severity'] ?? 'low'));
            if (in_array($sev, ['critical', 'high'], true)) {
                return true;
            }
        }

        return false;
    }

    private function appendAlertLog(string $line): void {
        @file_put_contents(self::ALERT_LOG_FILE, $line . PHP_EOL, FILE_APPEND);
    }

    private function saveScheduleConfig(array $config): void {
        @file_put_contents(self::SCHEDULE_CONFIG_FILE, (string)json_encode($config, JSON_PRETTY_PRINT));
        if (!file_exists(self::SCHEDULE_STATE_FILE)) {
            @file_put_contents(
                self::SCHEDULE_STATE_FILE,
                (string)json_encode(['last_run_at' => null, 'updated_at' => gmdate('c')], JSON_PRETTY_PRINT)
            );
        }
    }

    private function toBool(mixed $value): bool {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }
        if (is_int($value)) {
            return $value === 1;
        }

        return false;
    }

    private function getExportRecommendations(array $scanResult): array {
        $fromAutoFix = (array)($scanResult['auto_fix']['actions'] ?? []);
        $fromFindings = [];

        foreach ((array)($scanResult['findings'] ?? []) as $finding) {
            $severity = strtolower((string)($finding['severity'] ?? 'low'));
            $message = (string)($finding['message'] ?? '');
            if ($severity === 'critical' || $severity === 'high') {
                $fromFindings[] = 'Prioritize immediate remediation for: ' . $message;
            } elseif ($severity === 'medium') {
                $fromFindings[] = 'Schedule remediation in next patch cycle for: ' . $message;
            }
        }

        $generic = [
            'Re-run integrity scan after fixes to confirm zero critical/high findings.',
            'Keep scheduled scans enabled and review alerts daily.',
        ];

        $all = array_merge($fromAutoFix, $fromFindings, $generic);
        $all = array_values(array_unique(array_filter(array_map('strval', $all))));

        if (empty($all)) {
            return ['No recommendations. No findings detected.'];
        }

        return $all;
    }
}
