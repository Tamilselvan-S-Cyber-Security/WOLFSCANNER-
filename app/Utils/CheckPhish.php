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
 * CheckPhish API client for phishing URL detection.
 * Uses CheckPhish Neo API: https://developers.checkphish.ai/
 */
class CheckPhish {
    private const SCAN_URL = 'https://developers.checkphish.ai/api/neo/scan';
    private const STATUS_URL = 'https://developers.checkphish.ai/api/neo/scan/status';
    private const POLL_INTERVAL_SEC = 3;
    private const MAX_POLLS = 60; // 3 min max

    /**
     * Scan a URL and return the result.
     *
     * @return array{success: bool, disposition?: string, url?: string, brand?: string, insights?: string, error?: string, screenshot_path?: string, categories?: array}|array{success: false, error: string}
     */
    public static function scanUrl(string $url): array {
        $apiKey = Variables::getCheckPhishApiKey();
        if (!$apiKey) {
            return ['success' => false, 'error' => 'CheckPhish API key not configured. Add CHECKPHISH_API_KEY to config.'];
        }

        $url = trim($url);
        if ($url === '') {
            return ['success' => false, 'error' => 'URL is empty.'];
        }

        if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        $submit = self::submitUrl($apiKey, $url);
        if (isset($submit['error'])) {
            return ['success' => false, 'error' => $submit['error']];
        }

        $jobId = $submit['jobID'] ?? null;
        if (!$jobId) {
            return ['success' => false, 'error' => 'No job ID received from CheckPhish.'];
        }

        return self::pollForResult($apiKey, $jobId);
    }

    /**
     * @return array{jobID?: string}|array{error: string}
     */
    private static function submitUrl(string $apiKey, string $url): array {
        $payload = [
            'apiKey'  => $apiKey,
            'urlInfo' => ['url' => $url],
        ];

        $ch = curl_init(self::SCAN_URL);
        if ($ch === false) {
            return ['error' => 'cURL init failed.'];
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return ['error' => 'CheckPhish request failed.'];
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            return ['error' => 'Invalid CheckPhish response.'];
        }

        if (isset($data['errors'])) {
            $err = is_array($data['errors']) ? implode(', ', $data['errors']) : (string) $data['errors'];

            return ['error' => 'CheckPhish: ' . $err];
        }

        if ($httpCode === 401) {
            return ['error' => 'CheckPhish API key invalid or expired.'];
        }

        if ($httpCode !== 200 && $httpCode !== 201) {
            return ['error' => 'CheckPhish returned HTTP ' . $httpCode . '.'];
        }

        return $data;
    }

    /**
     * @return array{success: bool, disposition?: string, url?: string, brand?: string, insights?: string, error?: string, screenshot_path?: string, categories?: array}|array{success: false, error: string}
     */
    private static function pollForResult(string $apiKey, string $jobId): array {
        $payload = [
            'apiKey'   => $apiKey,
            'jobID'    => $jobId,
            'insights' => true,
        ];

        $polls = 0;

        while ($polls < self::MAX_POLLS) {
            $ch = curl_init(self::STATUS_URL);
            if ($ch === false) {
                return ['success' => false, 'error' => 'cURL init failed.'];
            }

            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false) {
                return ['success' => false, 'error' => 'CheckPhish status request failed.'];
            }

            $data = json_decode($response, true);
            if (!is_array($data)) {
                return ['success' => false, 'error' => 'Invalid CheckPhish response.'];
            }

            if (isset($data['errors'])) {
                $err = is_array($data['errors']) ? implode(', ', $data['errors']) : (string) $data['errors'];

                return ['success' => false, 'error' => 'CheckPhish: ' . $err];
            }

            $status = $data['status'] ?? '';

            if ($status === 'DONE') {
                $result = [
                    'success'         => true,
                    'disposition'     => $data['disposition'] ?? 'unknown',
                    'url'             => $data['url'] ?? '',
                    'brand'           => $data['brand'] ?? '',
                    'insights'        => $data['insights'] ?? '',
                    'screenshot_path' => $data['screenshot_path'] ?? '',
                    'categories'      => $data['categories'] ?? [],
                    'error'           => $data['error'] ?? '',
                ];

                return $result;
            }

            sleep(self::POLL_INTERVAL_SEC);
            $polls++;
        }

        return ['success' => false, 'error' => 'CheckPhish scan timed out. Try again later.'];
    }
}
