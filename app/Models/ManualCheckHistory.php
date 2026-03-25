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

class ManualCheckHistory extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_manual_check_history';

    public function getLastByOperatorId(int $operatorId, int $limit = 15): array {
        $params = [
            ':operator' => $operatorId,
            ':limit'    => $limit,
        ];

        $query = (
            'SELECT
                id,
                type,
                search_query,
                created_at
            FROM
                dshb_manual_check_history
            WHERE dshb_manual_check_history.operator = :operator
            ORDER BY created_at DESC
            LIMIT :limit'
        );

        return $this->execQuery($query, $params);
    }

    public function insertRecord(string $query, string $type, int $operatorId): void {
        $params = [
            ':query'    => $query,
            ':type'     => $type,
            ':operator' => $operatorId,
        ];

        $query = (
            'INSERT INTO dshb_manual_check_history (search_query, type, operator)
            VALUES (:query, :type, :operator)'
        );

        $this->execQuery($query, $params);
    }
}
