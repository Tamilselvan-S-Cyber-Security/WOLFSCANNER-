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

class ReviewQueue extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event_account';

    public function getCount(int $apiKey): int {
        $params = [
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(*) AS count

            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.fraud IS NOT TRUE AND
                event_account.added_to_review IS NOT NULL'
        );

        return $this->execQuery($query, $params)[0]['count'] ?? 0;
    }
}
