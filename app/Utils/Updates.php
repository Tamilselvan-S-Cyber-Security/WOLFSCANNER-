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

class Updates {
    private const UPDATES_LIST = [
        \Wolf\Updates\Update001::class,
        \Wolf\Updates\Update002::class,
        \Wolf\Updates\Update003::class,
        \Wolf\Updates\Update004::class,
        \Wolf\Updates\Update005::class,
        \Wolf\Updates\Update006::class,
        \Wolf\Updates\Update007::class,
        \Wolf\Updates\Update008::class,
    ];

    public static function syncUpdates(): void {
        $f3 = \Base::instance();
        $updates = new \Wolf\Models\Updates($f3);
        $applied = $updates->checkDb('core', self::UPDATES_LIST);

        if ($applied) {
            $controller = new \Wolf\Controllers\Admin\Rules\Data();
            // update only core rules
            $controller->updateRules(false);
        }

        \Wolf\Utils\Routes::callExtra('UPDATES');
    }
}
