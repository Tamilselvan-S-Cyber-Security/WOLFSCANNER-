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

class Domain extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event_domain';

    public function searchByDomain(string $query, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':query' => "%{$query}%",
        ];

        $query = (
            "SELECT
                event_domain.id     AS id,
                'Domain'             AS \"groupName\",
                'domain'             AS \"entityId\",
                event_domain.domain AS value

            FROM
                event_domain

            WHERE
                LOWER(event_domain.domain) LIKE LOWER(:query) AND
                event_domain.key = :api_key

            LIMIT 25 OFFSET 0"
        );

        return $this->execQuery($query, $params);
    }
}
