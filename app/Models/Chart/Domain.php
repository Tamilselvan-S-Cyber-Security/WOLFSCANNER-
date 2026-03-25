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

namespace Wolf\Models\Chart;

class Domain extends BaseEventsCount {
    public function getCounts(int $apiKey): array {
        $query = (
            "SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event.time + :offset))::bigint AS ts,
                COUNT(CASE WHEN event.type IN ({$this->normalFlatIds})  THEN TRUE END) AS event_normal_type_count,
                COUNT(CASE WHEN event.type IN ({$this->editFlatIds})    THEN TRUE END) AS event_editing_type_count,
                COUNT(CASE WHEN event.type IN ({$this->alertFlatIds})   THEN TRUE END) AS event_alert_type_count

            FROM
                event

            INNER JOIN event_email
            ON (event.email = event_email.id)

            WHERE
                event_email.domain = :id AND
                event.key = :api_key AND
                event.time >= :start_time AND
                event.time <= :end_time

            GROUP BY ts
            ORDER BY ts"
        );

        return $this->executeOnRangeById($query, $apiKey);
    }
}
