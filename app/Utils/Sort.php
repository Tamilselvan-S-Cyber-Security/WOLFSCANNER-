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

class Sort {
    public static function cmpTimestamp(array $left, array $right): int {
        return $left['ts'] - $right['ts'];
    }

    public static function cmpScore(array $left, array $right): int {
        return $right['score'] <=> $left['score'];
    }

    public static function cmpRule(array $left, array $right): int {
        if ($left['validated'] !== $right['validated']) {
            return ($right['validated'] <=> $left['validated']);
        }

        if (($left['missing'] === true) !== ($right['missing'] === true)) {
            return (\Wolf\Utils\Conversion::intVal($left['missing']) <=> \Wolf\Utils\Conversion::intVal($right['missing']));
        }

        return $left['uid'] <=> $right['uid'];
    }
}
