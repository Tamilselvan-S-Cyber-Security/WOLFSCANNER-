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

class Isp extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event_isp';

    public function searchByIsp(string $query, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':query' => "%{$query}%",
        ];

        $query = (
            "SELECT
                event_isp.id        AS id,
                'ASN'               AS \"groupName\",
                'isp'               AS \"entityId\",
                COALESCE(event_isp.asn::text, '') || COALESCE(' - ' || event_isp.name, '') AS value

            FROM
                event_isp

            WHERE
                (
                    LOWER(COALESCE(event_isp.asn::text, '')) LIKE LOWER(:query) OR
                    LOWER(COALESCE(event_isp.name, ''))      LIKE LOWER(:query)
                ) AND
                event_isp.key = :api_key

            LIMIT 25 OFFSET 0"
        );

        return $this->execQuery($query, $params);
    }
}
