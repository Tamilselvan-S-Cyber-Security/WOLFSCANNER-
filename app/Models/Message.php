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

class Message extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_message';

    public function addMessage(string $msg): int {
        $params = [
            ':text' => $msg,
        ];

        $query = (
            'INSERT INTO dshb_message (text) VALUES (:text) RETURNING id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'];
    }

    public function getLastMessage(): array {
        $query = (
            'SELECT
                id,
                text,
                title,
                created_at
            FROM
                dshb_message
            ORDER BY id DESC
            LIMIT 1'
        );

        $results = $this->execQuery($query, null);

        return $results[0] ?? [];
    }
}
