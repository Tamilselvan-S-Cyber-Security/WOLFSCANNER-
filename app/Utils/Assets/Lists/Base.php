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

namespace Wolf\Utils\Assets\Lists;

abstract class Base {
    protected static string $extensionFile = '';
    protected static array $list = [];
    protected static string $path = '/assets/lists/';

    protected static function getExtension(): ?array {
        $filename = dirname(__DIR__, 4) . static::$path . static::$extensionFile;

        if (file_exists($filename) && is_readable($filename)) {
            $data = include $filename;

            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }

    public static function getList(): array {
        return static::getExtension() ?? static::$list;
    }
}
