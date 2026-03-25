<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\Debugger;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    private const DEFAULT_TIMEOUT_SECONDS = 6;
    private const DEFAULT_MAX_BODY_BYTES = 500000;
    private const GEO_TIMEOUT_SECONDS = 4;

    public function proceedPostRequest(): array {
        return $this->debug();
    }

    private function debug(): array {
        $params = $this->extractRequestParams(['token', 'target', 'allow_private', 'keywords']);

        $pageParams = [
            'DEBUG_VALUES' => $params,
        ];

        try {
            $errorCode = $this->validateCsrfToken();
            if ($errorCode) {
                $pageParams['ERROR_CODE'] = $errorCode;
                return $pageParams;
            }

            $targetRaw = trim((string)($params['target'] ?? ''));
            if ($targetRaw === '') {
                $pageParams['ERROR_MESSAGE'] = 'Target URL is required.';
                return $pageParams;
            }

            $allowPrivate = ($params['allow_private'] ?? null) ? true : false;
            $keywordsRaw = trim((string)($params['keywords'] ?? ''));

            $parsed = $this->parseTarget($targetRaw);
            if (isset($parsed['error'])) {
                $pageParams['ERROR_MESSAGE'] = $parsed['error'];
                return $pageParams;
            }

            $normalized = $parsed['normalized'];
            $host = $parsed['host'] ?? null;
            $ip = null;

            if ($host) {
                $resolved = @gethostbyname($host);
                if ($resolved && $resolved !== $host) {
                    $ip = $resolved;
                }
            }

            if (!$allowPrivate) {
                $blockedReason = $this->getBlockedReason($host, $ip);
                if ($blockedReason !== null) {
                    $pageParams['ERROR_MESSAGE'] = $blockedReason;
                    return $pageParams;
                }
            }

            $http = $this->fetchUrl($normalized);
            if (isset($http['error']) && $http['error']) {
                $pageParams['ERROR_MESSAGE'] = 'Fetch failed: ' . $http['error'];
                return $pageParams;
            }

            $body = (string)($http['body'] ?? '');
            $headers = $http['headers'] ?? null;
            $status = $http['status'] ?? null;

            $keywords = $this->parseKeywords($keywordsRaw);
            $keywordMatches = $this->findKeywordMatches($body, $keywords);

            $findings = $this->analyzeStringsForIndicators($body);

            $geo = null;
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP) && !$this->isPrivateOrReservedIp($ip)) {
                $geo = $this->geoLookup($ip);
            }

            $report = [
                'target' => $normalized,
                'host' => $host,
                'ip' => $ip,
                'geo' => $geo,
                'status' => $status,
                'keywords' => $keywords,
                'keyword_matches' => $keywordMatches,
                'findings' => $findings,
            ];

            $this->f3->set('SESSION.debugger_last', [
                'html' => $body,
                'report' => $report,
            ]);

            $pageParams['DEBUG_RESULT'] = [
                'target' => $normalized,
                'host' => $host,
                'ip' => $ip,
                'geo' => $geo,
                'status' => $status,
                'keywords' => $keywords,
                'keywords_json' => htmlspecialchars(json_encode($keywords, JSON_UNESCAPED_SLASHES), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                'headers' => is_array($headers) ? implode("\n", $headers) : (string)$headers,
                'source_escaped' => htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                'keyword_matches' => $keywordMatches,
                'findings' => $findings,
            ];

            return $pageParams;
        } catch (\Throwable $e) {
            $pageParams['ERROR_MESSAGE'] = 'Debug failed: ' . $e->getMessage();
            return $pageParams;
        }
    }

    private function parseTarget(string $targetRaw): array {
        $withScheme = $targetRaw;
        if (!preg_match('~^https?://~i', $withScheme) && preg_match('~^[a-z0-9.-]+(?::\d+)?(?:/.*)?$~i', $withScheme)) {
            $withScheme = 'http://' . $withScheme;
        }

        $parts = @parse_url($withScheme);
        if (!$parts || !isset($parts['host'])) {
            return ['error' => 'Invalid target. Enter a URL (example.com/path).'];
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
            curl_setopt($ch, CURLOPT_USERAGENT, 'WolfScannerDebugger/1.0');
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
                'header' => "User-Agent: WolfScannerDebugger/1.0\r\n",
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

        $response['headers'] = $http_response_header ?? null;
        $response['body'] = $body;

        return $response;
    }

    private function parseKeywords(string $keywordsRaw): array {
        if ($keywordsRaw === '') {
            return [];
        }

        $parts = preg_split('~[\r\n,;]+~', $keywordsRaw);
        if (!is_array($parts)) {
            return [];
        }

        $out = [];
        foreach ($parts as $p) {
            $k = trim((string)$p);
            if ($k !== '') {
                $out[] = $k;
            }
        }

        return array_values(array_unique($out));
    }

    private function findKeywordMatches(string $body, array $keywords): array {
        $matches = [];

        foreach ($keywords as $keyword) {
            $count = 0;
            if ($keyword !== '') {
                $count = substr_count(strtolower($body), strtolower($keyword));
            }
            if ($count > 0) {
                $matches[] = [
                    'keyword' => $keyword,
                    'count' => $count,
                ];
            }
        }

        return $matches;
    }

    private function analyzeStringsForIndicators(string $text): array {
        $findings = [];
        $lower = strtolower($text);

        $rules = [
            'Possible SQL injection patterns' => ['union select', 'or 1=1', "'--", 'sleep(', 'benchmark('],
            'Possible XSS patterns' => ['<script', 'javascript:', 'onerror=', 'onload=', '<svg', 'document.cookie'],
            'Possible directory traversal patterns' => ['../', '..\\', '%2e%2e%2f', '%2e%2e%5c'],
            'Suspicious encoded payloads' => ['%3cscript%3e', '%27%20or%20%271%27%3d%271', 'base64,'],
            'CSRF indicators (forms without obvious tokens)' => ['<form', 'csrf'],
        ];

        foreach ($rules as $title => $needles) {
            foreach ($needles as $needle) {
                if ($needle === '') {
                    continue;
                }
                if (strpos($lower, strtolower($needle)) !== false) {
                    $findings[$title] = true;
                    break;
                }
            }
        }

        return $findings;
    }

    private function geoLookup(string $ip): ?array {
        $url = 'http://ip-api.com/json/' . rawurlencode($ip) . '?fields=status,message,country,countryCode,regionName,city,lat,lon,query';

        $context = stream_context_create([
            'http' => [
                'timeout' => self::GEO_TIMEOUT_SECONDS,
                'method' => 'GET',
                'header' => "User-Agent: WolfScannerDebugger/1.0\r\n",
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false || $raw === '') {
            return null;
        }

        $data = @json_decode($raw, true);
        if (!is_array($data)) {
            return null;
        }

        if (($data['status'] ?? null) !== 'success') {
            return null;
        }

        if (!isset($data['lat'], $data['lon'])) {
            return null;
        }

        $lat = (float)$data['lat'];
        $lon = (float)$data['lon'];

        return [
            'ip' => (string)($data['query'] ?? $ip),
            'country' => (string)($data['country'] ?? ''),
            'country_code' => (string)($data['countryCode'] ?? ''),
            'region' => (string)($data['regionName'] ?? ''),
            'city' => (string)($data['city'] ?? ''),
            'lat' => $lat,
            'lon' => $lon,
        ];
    }
}
