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

class VersionControl {
    public const VERSION_MAJOR = 0;
    public const VERSION_MINOR = 9;
    public const VERSION_REVISION = 12;

    public static function versionString(): string {
        return sprintf('%d.%d.%d', self::VERSION_MAJOR, self::VERSION_MINOR, self::VERSION_REVISION);
    }

    public static function fullVersionString(): string {
        return sprintf('v%d.%d.%d', self::VERSION_MAJOR, self::VERSION_MINOR, self::VERSION_REVISION);
    }
}
