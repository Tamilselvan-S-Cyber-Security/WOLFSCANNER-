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
 * Stores and retrieves total count of reports sent from Report Sender page.
 * Persists per api_key in a JSON file for future reference.
 */
class ReportSenderCount
{
    private const FILE_NAME = 'report_sender_count.json';
    private const DATA_DIR  = 'data';

    private static function getFilePath(): string
    {
        $base = \Base::instance()->get('ROOT') ?: (__DIR__ . '/../../');
        $dir  = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . self::DATA_DIR;

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir . DIRECTORY_SEPARATOR . self::FILE_NAME;
    }

    private static function readData(): array
    {
        $path = self::getFilePath();

        if (!is_file($path)) {
            return [];
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function writeData(array $data): bool
    {
        $path = self::getFilePath();
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX) !== false;
    }

    /**
     * Get current report send count for the given api key (or default key).
     */
    public static function getCount(?int $apiKeyId = null): int
    {
        $key = $apiKeyId !== null ? (string) $apiKeyId : 'default';
        $data = self::readData();

        return isset($data[$key]) ? (int) $data[$key] : 0;
    }

    /**
     * Increment report send count and return the new value.
     */
    public static function increment(?int $apiKeyId = null): int
    {
        $key  = $apiKeyId !== null ? (string) $apiKeyId : 'default';
        $data = self::readData();
        $data[$key] = (int) ($data[$key] ?? 0) + 1;
        self::writeData($data);

        return (int) $data[$key];
    }
}
