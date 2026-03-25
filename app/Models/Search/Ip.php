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

namespace Wolf\Models\Search;

class Ip extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event_ip';

    public function searchByIp(string $query, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':query' => "%{$query}%",
        ];

        $query = (
            "SELECT
                event_ip.id AS id,
                'IP'        AS \"groupName\",
                'ip'        AS \"entityId\",
                event_ip.ip AS value

            FROM
                event_ip

            WHERE
                LOWER(TEXT(event_ip.ip)) LIKE LOWER(:query) AND
                event_ip.key = :api_key

            LIMIT 25 OFFSET 0"
        );

        return $this->execQuery($query, $params);
    }
}
