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

namespace Wolf\Updates;

class Update001 extends Base {
    public static string $version = 'v0.9.5';

    public static function apply(\DB\SQL $database): void {
        $queries = [
            'ALTER TABLE dshb_api ADD COLUMN blacklist_threshold INTEGER DEFAULT -1',
            'ALTER TABLE dshb_api ADD COLUMN review_queue_threshold INTEGER DEFAULT 33',
        ];
        foreach ($queries as $sql) {
            $database->exec($sql);
        }
    }
}
