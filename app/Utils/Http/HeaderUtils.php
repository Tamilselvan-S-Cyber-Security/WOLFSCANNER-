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

namespace Wolf\Utils\Http;

final class HeaderUtils {
    /**
     * Ensures the header exists (case-insensitive by name).
     *
     * @param array<int, string> $headers
     *
     * @return array<int, string>
     */
    public static function ensureHeader(array $headers, string $name, string $value): array {
        $needle = strtolower($name) . ':';

        foreach ($headers as $headerLine) {
            $line = strtolower(trim($headerLine));

            if (str_starts_with($line, $needle)) {
                return $headers;
            }
        }

        $header = sprintf('%s: %s', $name, $value);
        $headers[] = $header;

        return $headers;
    }
}
