<?php

/**
 * Wolf Security scanner ~ open-source security framework
 * Copyright (c) Wolf Security scanner Team Sàrl (https://www.cyberwolf.pro)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Wolf Security scanner Team Sàrl (https://www.cyberwolf.pro)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cyberwolf.pro Wolf Security scanner
 */

declare(strict_types=1);

namespace Wolf\Utils;

/**
 * Ollama API client for tinyllama:latest.
 * Sends prompts with Wolf scanner cybersecurity context.
 */
class Ollama {
    private const MODEL = 'tinyllama:latest';
    private const DEFAULT_URL = 'http://localhost:11434';

    /**
     * Wolf scanner + SIEM + cybersecurity knowledge base.
     */
    private const SYSTEM_PROMPT = 'You are Cyber Bot, a SIEM and cybersecurity AI assistant for Wolf scanner. ' .
        'You have expertise in: (1) SIEM – log correlation, alert triage, detection rules, MITRE ATT&CK, event normalization, ' .
        'incident response, threat hunting; (2) Cybersecurity – vulnerabilities, malware, phishing, bot detection, fraud, ' .
        'OWASP, compliance (GDPR, PCI-DSS), hardening; (3) Wolf scanner – review queue, blacklist, rules engine, risk scoring, ' .
        'activity monitoring, integrity scanner, API keys, enrichment. ' .
        'IMPORTANT: Keep answers short – 2 to 4 lines max. Be direct and actionable. One idea per line. No long paragraphs.';

    public static function chat(string $userMessage): ?string {
        $url = self::getBaseUrl() . '/api/chat';
        $payload = [
            'model' => self::MODEL,
            'messages' => [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'stream' => false,
            'options' => ['num_predict' => 200],
        ];

        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $data = json_decode($response, true);
        $content = $data['message']['content'] ?? null;

        return is_string($content) ? trim($content) : null;
    }

    /**
     * Check if Ollama is reachable and tinyllama model is available.
     */
    public static function isConnected(): bool {
        $url = self::getBaseUrl() . '/api/tags';
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return false;
        }

        $data = json_decode($response, true);
        $models = $data['models'] ?? [];
        $modelName = self::MODEL;

        foreach ($models as $m) {
            $name = $m['name'] ?? '';
            if ($name === $modelName || str_starts_with($name, $modelName . ':')) {
                return true;
            }
        }

        return false;
    }

    private static function getBaseUrl(): string {
        return \Wolf\Utils\Variables::getOllamaUrl();
    }
}
