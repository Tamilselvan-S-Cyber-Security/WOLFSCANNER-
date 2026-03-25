<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\Vuln;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    private const DEFAULT_TIMEOUT_SECONDS = 6;
    private const DEFAULT_MAX_BODY_BYTES = 200000;
    private const HISTORY_FILE = 'tmp/vuln_scan_history.json';
    private const HISTORY_LIMIT = 60;

    public function proceedPostRequest(): array {
        return $this->scan();
    }

    private function scan(): array {
        $params = $this->extractRequestParams(['token', 'target', 'allow_private', 'fetch', 'aggressive']);

        $pageParams = [
            'SCAN_VALUES' => $params,
        ];

        try {
            $errorCode = $this->validateCsrfToken();
            if ($errorCode) {
                $pageParams['ERROR_CODE'] = $errorCode;
                return $pageParams;
            }

            $targetRaw = trim((string)($params['target'] ?? ''));
            if ($targetRaw === '') {
                $pageParams['ERROR_MESSAGE'] = 'Target is required.';
                return $pageParams;
            }

            $allowPrivate = ($params['allow_private'] ?? null) ? true : false;
            $doFetch = ($params['fetch'] ?? null) ? true : false;
            $aggressive = ($params['aggressive'] ?? null) ? true : false;

            $parsed = $this->parseTarget($targetRaw);
            if (isset($parsed['error'])) {
                $pageParams['ERROR_MESSAGE'] = $parsed['error'];
                return $pageParams;
            }

            $targetType = $parsed['type'];
            $normalized = $parsed['normalized'];
            $host = $parsed['host'] ?? null;
            $ip = $parsed['ip'] ?? null;

            if (!$allowPrivate) {
                $blockedReason = $this->getBlockedReason($host, $ip);
                if ($blockedReason !== null) {
                    $pageParams['ERROR_MESSAGE'] = $blockedReason;
                    return $pageParams;
                }
            }

            $result = [
                'target' => $normalized,
                'type' => $targetType,
                'host' => $host,
                'ip' => $ip,
                'reverse_dns' => null,
                'whois' => null,
                'blacklisted' => null,
                'has_blacklisted' => false,
                'blacklisted_text' => null,
                'http' => null,
                'has_http' => false,
                'findings' => [],
                'summary' => [
                    'critical' => 0,
                    'high' => 0,
                    'medium' => 0,
                    'low' => 0,
                    'total' => 0,
                ],
                'risk_score' => 0,
                'risk_level' => 'Low',
                'recommendations' => [],
            ];

            if ($host) {
                $resolved = gethostbyname($host);
                if ($resolved && $resolved !== $host) {
                    $result['ip'] = $resolved;
                    $ip = $resolved;
                }
            }

            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                $result['reverse_dns'] = @gethostbyaddr($ip) ?: null;
                $result['blacklisted'] = $this->isIpBlacklistedInWolf($ip);
                if ($result['blacklisted'] !== null) {
                    $result['has_blacklisted'] = true;
                    $result['blacklisted_text'] = $result['blacklisted'] ? 'Yes' : 'No';
                }
                $result['whois'] = $this->whoisLookup($ip);
            } elseif ($host) {
                $result['whois'] = $this->whoisLookup($host);
            }

            $result['findings'] = $this->analyzeStringsForIndicators($normalized, 'target', $aggressive);

            if ($doFetch && $targetType === 'url') {
                $http = $this->fetchUrl($normalized);
                $result['http'] = $http;
                if ((isset($http['error']) && $http['error']) || (isset($http['status']) && $http['status'])) {
                    $result['has_http'] = true;
                }
                if (isset($http['headers']) && is_string($http['headers'])) {
                    $headerFindings = $this->analyzeHeadersForIndicators($http['headers']);
                    $result['findings'] = $this->mergeFindings($result['findings'], $headerFindings);
                }
                if (isset($http['body'])) {
                    $bodyFindings = $this->analyzeStringsForIndicators($http['body'], 'http_body', $aggressive);
                    $result['findings'] = $this->mergeFindings($result['findings'], $bodyFindings);
                }
            }

            if (($result['blacklisted'] ?? false) === true) {
                $result['findings'] = $this->mergeFindings($result['findings'], [[
                    'id' => 'wolf_blacklisted_ip',
                    'category' => 'reputation',
                    'severity' => 'critical',
                    'message' => 'Target IP is already marked as blacklisted in Wolf.',
                    'source' => 'reputation',
                ]]);
            }

            $result['summary'] = $this->summarizeFindings($result['findings']);
            $result['risk_score'] = $this->calculateRiskScore($result['summary']);
            $result['risk_level'] = $this->riskLevelFromScore($result['risk_score']);
            $result['recommendations'] = $this->buildRecommendations($result);
            $result['generated_at'] = gmdate('c');

            $trend = $this->appendHistoryAndBuildTrend($result['target'], $result['risk_score'], $result['summary']['total']);
            $pageParams['SCAN_TREND'] = $trend;

            $pageParams['SCAN_RESULT'] = $result;
            return $pageParams;
        } catch (\Throwable $e) {
            $pageParams['ERROR_MESSAGE'] = 'Scan failed: ' . $e->getMessage();
            return $pageParams;
        }
    }

    private function parseTarget(string $targetRaw): array {
        $isIp = filter_var($targetRaw, FILTER_VALIDATE_IP) !== false;
        if ($isIp) {
            return [
                'type' => 'ip',
                'normalized' => $targetRaw,
                'ip' => $targetRaw,
            ];
        }

        $withScheme = $targetRaw;
        if (!preg_match('~^https?://~i', $withScheme) && preg_match('~^[a-z0-9.-]+(?::\d+)?(?:/.*)?$~i', $withScheme)) {
            $withScheme = 'http://' . $withScheme;
        }

        $parts = @parse_url($withScheme);
        if (!$parts || !isset($parts['host'])) {
            return ['error' => 'Invalid target. Enter a URL (example.com/path) or an IP.'];
        }

        $scheme = strtolower((string)($parts['scheme'] ?? 'http'));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return ['error' => 'Only http/https URLs are allowed.'];
        }

        $host = (string)$parts['host'];
        $path = (string)($parts['path'] ?? '');
        $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
        $port = isset($parts['port']) ? (':' . $parts['port']) : '';

        $normalized = $scheme . '://' . $host . $port . $path . $query;

        return [
            'type' => 'url',
            'normalized' => $normalized,
            'host' => $host,
        ];
    }

    private function getBlockedReason(?string $host, ?string $ip): ?string {
        if ($host) {
            $hostLower = strtolower($host);
            if ($hostLower === 'localhost' || $this->endsWith($hostLower, '.localhost')) {
                return 'Blocked target: localhost is not allowed.';
            }
        }

        if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
            if ($this->isPrivateOrReservedIp($ip)) {
                return 'Blocked target: private/reserved IP is not allowed.';
            }
        }

        if ($host) {
            $resolved = @gethostbyname($host);
            if ($resolved && filter_var($resolved, FILTER_VALIDATE_IP) && $this->isPrivateOrReservedIp($resolved)) {
                return 'Blocked target: host resolves to private/reserved IP.';
            }
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

    private function fetchUrl(string $url): array {
        $response = [
            'status' => null,
            'headers' => null,
            'body' => null,
            'error' => null,
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::DEFAULT_TIMEOUT_SECONDS);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::DEFAULT_TIMEOUT_SECONDS);
            curl_setopt($ch, CURLOPT_USERAGENT, 'WolfScannerAutoScanner/1.0');
            curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
            curl_setopt($ch, CURLOPT_HEADER, true);

            $raw = curl_exec($ch);
            if ($raw === false) {
                $response['error'] = curl_error($ch);
                curl_close($ch);
                return $response;
            }

            $headerSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $headersRaw = substr($raw, 0, $headerSize);
            $body = substr($raw, $headerSize);

            if (strlen($body) > self::DEFAULT_MAX_BODY_BYTES) {
                $body = substr($body, 0, self::DEFAULT_MAX_BODY_BYTES);
            }

            $response['status'] = $status;
            $response['headers'] = $headersRaw;
            $response['body'] = $body;

            return $response;
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => self::DEFAULT_TIMEOUT_SECONDS,
                'method' => 'GET',
                'header' => "User-Agent: WolfScannerAutoScanner/1.0\r\n",
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            $response['error'] = 'Request failed.';
            return $response;
        }

        if (strlen($body) > self::DEFAULT_MAX_BODY_BYTES) {
            $body = substr($body, 0, self::DEFAULT_MAX_BODY_BYTES);
        }

        $response['status'] = null;
        $response['headers'] = $http_response_header ?? null;
        $response['body'] = $body;

        return $response;
    }

    private function analyzeStringsForIndicators(string $text, string $source, bool $aggressive = false): array {
        $findings = [];

        $rules = [
            [
                'id' => 'xss_script_tag',
                'category' => 'xss',
                'severity' => 'high',
                'message' => 'XSS indicator: script tag payload detected.',
                'regex' => '~<\s*script\b~i',
            ],
            [
                'id' => 'xss_event_handler',
                'category' => 'xss',
                'severity' => 'high',
                'message' => 'XSS indicator: inline JS event handler detected.',
                'regex' => '~\bon(?:error|load|click|focus)\s*=~i',
            ],
            [
                'id' => 'xss_js_proto',
                'category' => 'xss',
                'severity' => 'high',
                'message' => 'XSS indicator: javascript: URI detected.',
                'regex' => '~javascript:~i',
            ],
            [
                'id' => 'sqli_union_select',
                'category' => 'sqli',
                'severity' => 'critical',
                'message' => 'SQLi indicator: UNION SELECT payload detected.',
                'regex' => '~\bunion\b\s+\bselect\b~i',
            ],
            [
                'id' => 'sqli_tautology',
                'category' => 'sqli',
                'severity' => 'critical',
                'message' => 'SQLi indicator: tautology payload detected.',
                'regex' => "~(?:\bor\b\s+1=1\b|'\s*or\s*'1'='1)~i",
            ],
            [
                'id' => 'sqli_drop_table',
                'category' => 'sqli',
                'severity' => 'critical',
                'message' => 'SQLi indicator: DROP TABLE payload detected.',
                'regex' => '~\bdrop\b\s+\btable\b~i',
            ],
            [
                'id' => 'dir_traversal',
                'category' => 'path_traversal',
                'severity' => 'high',
                'message' => 'Path traversal indicator detected.',
                'regex' => '~(\.\./|\.\.\\\\|%2e%2e%2f|%2e%2e%5c)~i',
            ],
            [
                'id' => 'command_injection',
                'category' => 'command_injection',
                'severity' => 'high',
                'message' => 'Command injection indicator detected.',
                'regex' => '~(;|\|\||&&|`|\$\()~',
            ],
            [
                'id' => 'suspicious_commands',
                'category' => 'command_injection',
                'severity' => 'high',
                'message' => 'System command pattern detected in payload.',
                'regex' => '~\b(curl|wget|powershell|cmd\.exe|bash|nc|ncat)\b~i',
            ],
            [
                'id' => 'ssti',
                'category' => 'template_injection',
                'severity' => 'medium',
                'message' => 'SSTI indicator detected (template expression).',
                'regex' => '~(\{\{.*\}\}|\$\{.*\}|<%.*%>)~s',
            ],
            [
                'id' => 'secret_leak',
                'category' => 'data_exposure',
                'severity' => 'high',
                'message' => 'Possible credential/token leak pattern detected.',
                'regex' => '~(api[_-]?key|secret|password|token)\s*[:=]\s*[\'"][^\'"]{8,}[\'"]~i',
            ],
            [
                'id' => 'encoded_attack',
                'category' => 'encoded_payload',
                'severity' => 'medium',
                'message' => 'Encoded attack payload marker detected.',
                'regex' => '~(%3cscript%3e|%3c%2fscript%3e|%27\s*or\s*1%3d1|%2fetc%2fpasswd)~i',
            ],
        ];

        if ($aggressive) {
            $rules[] = [
                'id' => 'rce_keywords',
                'category' => 'rce',
                'severity' => 'critical',
                'message' => 'Potential RCE keyword pattern detected.',
                'regex' => '~\b(eval|exec|shell_exec|passthru|proc_open|popen)\b~i',
            ];
            $rules[] = [
                'id' => 'lfi_indicator',
                'category' => 'lfi',
                'severity' => 'high',
                'message' => 'Local file inclusion marker detected.',
                'regex' => '~(php://|file://|/etc/passwd|boot\.ini|win\.ini)~i',
            ];
        }

        foreach ($rules as $rule) {
            if (@preg_match($rule['regex'], $text) === 1) {
                $findings[] = [
                    'id' => $rule['id'],
                    'category' => $rule['category'],
                    'severity' => $rule['severity'],
                    'message' => $rule['message'],
                    'source' => $source,
                ];
            }
        }

        return $findings;
    }

    private function analyzeHeadersForIndicators(string $headers): array {
        $h = strtolower($headers);
        $findings = [];

        if (!str_contains($h, 'content-security-policy:')) {
            $findings[] = [
                'id' => 'missing_csp',
                'category' => 'security_headers',
                'severity' => 'medium',
                'message' => 'Missing Content-Security-Policy header.',
                'source' => 'http_headers',
            ];
        }
        if (!str_contains($h, 'x-frame-options:') && !str_contains($h, 'frame-ancestors')) {
            $findings[] = [
                'id' => 'missing_clickjack_protection',
                'category' => 'security_headers',
                'severity' => 'medium',
                'message' => 'Missing clickjacking protection header.',
                'source' => 'http_headers',
            ];
        }
        if (!str_contains($h, 'x-content-type-options: nosniff')) {
            $findings[] = [
                'id' => 'missing_nosniff',
                'category' => 'security_headers',
                'severity' => 'low',
                'message' => 'Missing X-Content-Type-Options: nosniff.',
                'source' => 'http_headers',
            ];
        }
        if (!str_contains($h, 'strict-transport-security:')) {
            $findings[] = [
                'id' => 'missing_hsts',
                'category' => 'security_headers',
                'severity' => 'low',
                'message' => 'Missing Strict-Transport-Security header.',
                'source' => 'http_headers',
            ];
        }

        return $findings;
    }

    private function mergeFindings(array $a, array $b): array {
        $seen = [];
        foreach ($a as $rec) {
            if (!is_array($rec)) {
                continue;
            }
            $key = ($rec['id'] ?? '') . '|' . ($rec['source'] ?? '');
            $seen[$key] = true;
        }

        foreach ($b as $rec) {
            if (!is_array($rec)) {
                continue;
            }
            $key = ($rec['id'] ?? '') . '|' . ($rec['source'] ?? '');
            if (!isset($seen[$key])) {
                $a[] = $rec;
                $seen[$key] = true;
            }
        }

        return $a;
    }

    private function summarizeFindings(array $findings): array {
        $summary = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'total' => 0,
        ];

        foreach ($findings as $finding) {
            if (!is_array($finding)) {
                continue;
            }
            $sev = strtolower((string)($finding['severity'] ?? 'low'));
            if (!isset($summary[$sev])) {
                $sev = 'low';
            }
            $summary[$sev]++;
            $summary['total']++;
        }

        return $summary;
    }

    private function calculateRiskScore(array $summary): int {
        $score =
            ((int)$summary['critical'] * 25) +
            ((int)$summary['high'] * 15) +
            ((int)$summary['medium'] * 8) +
            ((int)$summary['low'] * 3);

        return min(100, $score);
    }

    private function riskLevelFromScore(int $score): string {
        if ($score >= 75) {
            return 'Critical';
        }
        if ($score >= 50) {
            return 'High';
        }
        if ($score >= 25) {
            return 'Medium';
        }
        return 'Low';
    }

    private function buildRecommendations(array $result): array {
        $recs = [];
        $summary = $result['summary'] ?? [];
        if (($summary['critical'] ?? 0) > 0) {
            $recs[] = 'Critical indicators found: block/triage this target immediately.';
        }
        if (($summary['high'] ?? 0) > 0) {
            $recs[] = 'Apply WAF rules and input validation for detected payload types.';
        }
        if (($summary['medium'] ?? 0) > 0) {
            $recs[] = 'Review application logs and sanitize user-controlled outputs.';
        }
        if (isset($result['has_http']) && $result['has_http']) {
            $recs[] = 'Harden security headers (CSP, HSTS, X-Frame-Options, nosniff).';
        }
        if (isset($result['has_blacklisted']) && $result['has_blacklisted'] && ($result['blacklisted'] ?? false)) {
            $recs[] = 'Investigate related sessions/accounts tied to this blacklisted IP.';
        }
        if (empty($recs)) {
            $recs[] = 'No major indicators found. Continue periodic monitoring.';
        }

        return array_values(array_unique($recs));
    }

    private function appendHistoryAndBuildTrend(string $target, int $score, int $totalFindings): array {
        $history = $this->readHistory();
        $history[] = [
            'time' => gmdate('c'),
            'target' => $target,
            'score' => $score,
            'findings' => $totalFindings,
        ];
        if (count($history) > self::HISTORY_LIMIT) {
            $history = array_slice($history, -self::HISTORY_LIMIT);
        }
        $this->writeHistory($history);

        $labels = [];
        $scores = [];
        foreach (array_slice($history, -20) as $row) {
            $labels[] = (string)date('H:i', strtotime((string)$row['time']));
            $scores[] = (int)($row['score'] ?? 0);
        }

        return $this->buildLineGraph($labels, $scores);
    }

    private function buildLineGraph(array $labels, array $scores): array {
        $width = 760;
        $height = 220;
        $padL = 46;
        $padR = 20;
        $padT = 16;
        $padB = 34;
        $plotW = $width - $padL - $padR;
        $plotH = $height - $padT - $padB;

        if (empty($scores)) {
            return [
                'width' => $width,
                'height' => $height,
                'points' => '',
                'labels' => [],
                'scores' => [],
            ];
        }

        $points = [];
        $count = count($scores);
        foreach ($scores as $i => $value) {
            $x = $padL + ($count > 1 ? (($plotW * $i) / ($count - 1)) : ($plotW / 2));
            $normalized = max(0, min(100, (int)$value));
            $y = $padT + $plotH - (($plotH * $normalized) / 100);
            $points[] = round($x, 1) . ',' . round($y, 1);
        }

        return [
            'width' => $width,
            'height' => $height,
            'points' => implode(' ', $points),
            'labels' => $labels,
            'scores' => $scores,
        ];
    }

    private function readHistory(): array {
        if (!is_file(self::HISTORY_FILE)) {
            return [];
        }
        $raw = @file_get_contents(self::HISTORY_FILE);
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function writeHistory(array $history): void {
        @file_put_contents(self::HISTORY_FILE, (string)json_encode($history, JSON_PRETTY_PRINT));
    }

    private function isIpBlacklistedInWolf(string $ip): ?bool {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        if (!$apiKey) {
            return null;
        }

        $query = (
            'SELECT 1\n'
            . 'FROM event_ip\n'
            . 'WHERE\n'
            . '    key = :key AND\n'
            . '    split_part(event_ip.ip::text, \'/\', 1) = :ip AND\n'
            . '    event_ip.fraud_detected IS TRUE\n'
            . 'LIMIT 1'
        );

        try {
            $db = \Wolf\Utils\Database::getDb();
            if (!$db) {
                return null;
            }

            $res = $db->exec($query, [':key' => $apiKey, ':ip' => $ip]);
            return is_array($res) && count($res) > 0;
        } catch (\Throwable) {
            return null;
        }
    }

    private function whoisLookup(string $query): ?string {
        $query = trim($query);
        if ($query === '') {
            return null;
        }

        $server = 'whois.iana.org';
        $resp = $this->rawWhois($server, $query);
        if ($resp === null) {
            return null;
        }

        if (preg_match('/^refer:\s*(.+)$/mi', $resp, $m)) {
            $ref = trim($m[1]);
            $resp2 = $this->rawWhois($ref, $query);
            if ($resp2 !== null) {
                $resp = $resp2;
            }
        }

        if (strlen($resp) > 6000) {
            $resp = substr($resp, 0, 6000);
        }

        return $resp;
    }

    private function rawWhois(string $server, string $query): ?string {
        $fp = @fsockopen($server, 43, $errno, $errstr, 4);
        if (!$fp) {
            return null;
        }

        stream_set_timeout($fp, 4);
        fwrite($fp, $query . "\r\n");

        $out = '';
        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            if ($line === false) {
                break;
            }
            $out .= $line;
            if (strlen($out) > 7000) {
                break;
            }
        }

        fclose($fp);
        return $out;
    }
}
