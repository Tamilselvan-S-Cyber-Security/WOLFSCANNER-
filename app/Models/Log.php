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

namespace Wolf\Models;

class Log extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_logs';

    public function insertRecord(array $data): void {
        $params = [
            ':text' => json_encode($data),
        ];

        $query = (
            'INSERT INTO dshb_logs (text) VALUES (:text)'
        );

        $this->execQuery($query, $params);
    }
}
