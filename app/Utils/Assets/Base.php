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

namespace Wolf\Utils\Assets;

abstract class Base {
    protected static function includeOnce(string $filename): mixed {
        return include_once $filename;
    }

    protected static function log(string $message): void {
        error_log($message);
    }

    abstract protected static function getClassFilename(string $filename): string;

    abstract protected static function getDirectory(): string;

    abstract protected static function getNamespace(): string;

    protected static function validateClass(string $filename, string $classname): object {
        if (!file_exists($filename)) {
            throw new \LogicException("File {$filename} doesn't exist.");
        }

        $res = self::includeOnce($filename);

        if ($res === false) {
            throw new \LogicException("Class {$classname} was not included due to include error for file {$filename}");
        }

        if (!class_exists($classname, false)) {
            throw new \LogicException("Class {$classname} not found after including {$filename}");
        }

        $reflection = new \ReflectionClass($classname);
        $reflectionFileName = $reflection->getFileName();

        if (realpath($reflectionFileName) !== realpath($filename)) {
            throw new \LogicException("Class {$classname} is defined in {$reflectionFileName}, not in {$filename}");
        }

        return $reflection;
    }
}
