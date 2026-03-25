<?php

declare(strict_types=1);

namespace Wolf\Crons;

class IntegrityAutoScanner extends Base {
    private const SCHEDULE_CONFIG_FILE = 'tmp/integrity_auto_scanner_schedule.json';
    private const SCHEDULE_STATE_FILE = 'tmp/integrity_auto_scanner_state.json';
    private const ALERT_LOG_FILE = 'assets/logs/integrity_alerts.log';
    private const DEFAULT_INTERVAL_MINUTES = 60;
    private const MAX_FILE_BYTES = 250000;

    public function process(): void {
        $config = $this->readJson(self::SCHEDULE_CONFIG_FILE);
        if (!is_array($config) || !($config['enabled'] ?? false)) {
            $this->addLog('Integrity auto scanner schedule is disabled.');
            return;
        }

        $intervalMinutes = (int)($config['interval_minutes'] ?? self::DEFAULT_INTERVAL_MINUTES);
        $intervalMinutes = max(1, min(1440, $intervalMinutes));
        $intervalSeconds = $intervalMinutes * 60;

        $state = $this->readJson(self::SCHEDULE_STATE_FILE);
        $lastRunAt = is_array($state) ? (string)($state['last_run_at'] ?? '') : '';
        $lastRunTs = $lastRunAt !== '' ? strtotime($lastRunAt) : false;
        $now = time();

        if ($lastRunTs !== false && ($now - $lastRunTs) < $intervalSeconds) {
            $this->addLog('Skipping integrity auto scanner run (interval not reached).');
            return;
        }

        $findings = $this->scanProjectQuick();
        $summary = $this->summarize($findings);

        $this->writeJson(self::SCHEDULE_STATE_FILE, [
            'last_run_at' => gmdate('c'),
            'updated_at' => gmdate('c'),
            'last_summary' => $summary,
        ]);

        $this->addLog(sprintf(
            'Integrity auto scanner finished: total=%d, critical=%d, high=%d, medium=%d, low=%d',
            $summary['total'],
            $summary['critical'],
            $summary['high'],
            $summary['medium'],
            $summary['low']
        ));

        if (($config['enable_alerts'] ?? false) && ($summary['critical'] > 0 || $summary['high'] > 0)) {
            $line = sprintf(
                '[%s] [IntegrityAutoScanner] critical=%d high=%d medium=%d low=%d',
                gmdate('c'),
                $summary['critical'],
                $summary['high'],
                $summary['medium'],
                $summary['low']
            );
            @file_put_contents(self::ALERT_LOG_FILE, $line . PHP_EOL, FILE_APPEND);
            $this->addLog('Integrity alert written to log.');
        }
    }

    private function scanProjectQuick(): array {
        $rules = [
            ['id' => 'php_eval', 'severity' => 'high', 'pattern' => '/\beval\s*\(/i'],
            ['id' => 'php_system', 'severity' => 'high', 'pattern' => '/\b(system|exec|shell_exec|passthru|proc_open)\s*\(/i'],
            ['id' => 'php_preg_replace_eval', 'severity' => 'high', 'pattern' => '/preg_replace\s*\(\s*[\'"][^\'"]*\/e[\'"]/i'],
            ['id' => 'php_obfuscation', 'severity' => 'medium', 'pattern' => '/\b(base64_decode|gzinflate|gzuncompress)\s*\(/i'],
        ];

        $targets = ['app', 'sensor', 'ui', 'tmp'];
        $allowedExt = ['php', 'phtml', 'js', 'html', 'htm'];
        $findings = [];

        foreach ($targets as $target) {
            if (!is_dir($target)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($target, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $item) {
                if (!$item instanceof \SplFileInfo || !$item->isFile()) {
                    continue;
                }

                $path = str_replace('\\', '/', $item->getPathname());
                $ext = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    continue;
                }

                $size = (int)$item->getSize();
                if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
                    continue;
                }

                $content = @file_get_contents($path);
                if (!is_string($content) || $content === '') {
                    continue;
                }

                foreach ($rules as $rule) {
                    if (@preg_match($rule['pattern'], $content) === 1) {
                        $findings[] = [
                            'file' => $path,
                            'rule' => $rule['id'],
                            'severity' => $rule['severity'],
                        ];
                    }
                }
            }
        }

        return $findings;
    }

    private function summarize(array $findings): array {
        $summary = ['total' => 0, 'critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
        foreach ($findings as $finding) {
            $summary['total']++;
            $sev = strtolower((string)($finding['severity'] ?? 'low'));
            if (isset($summary[$sev])) {
                $summary[$sev]++;
            }
        }

        return $summary;
    }

    private function readJson(string $path): mixed {
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            return null;
        }

        return json_decode($raw, true);
    }

    private function writeJson(string $path, array $payload): void {
        @file_put_contents($path, (string)json_encode($payload, JSON_PRETTY_PRINT));
    }
}
