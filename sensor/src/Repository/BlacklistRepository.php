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

namespace Sensor\Repository;

use Sensor\Type\BlacklistType;

class BlacklistRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function isBlacklisted(int $apiKeyId, string $type, string $value): bool {
        $sql = '';
        switch ($type) {
            case BlacklistType::Ip:
                $sql = 'SELECT 1
                    FROM event_ip
                    WHERE
                        key = :key AND
                        event_ip.ip = :value AND
                        event_ip.fraud_detected IS TRUE
                        LIMIT 1;
                ';
                break;

            case BlacklistType::Email:
                $sql = 'SELECT 1
                    FROM event_email
                    WHERE
                        key = :key AND
                        event_email.email = :value AND
                        event_email.fraud_detected IS TRUE
                        LIMIT 1;
                ';
                break;

            case BlacklistType::Phone:
                $sql = 'SELECT 1
                    FROM event_phone
                    WHERE
                        key = :key AND
                        event_phone.phone_number = :value AND
                        event_phone.fraud_detected IS TRUE
                        LIMIT 1;
                ';
                break;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $apiKeyId);
        $stmt->bindValue(':value', $value);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }
}
