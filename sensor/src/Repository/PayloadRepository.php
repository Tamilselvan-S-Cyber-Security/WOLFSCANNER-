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

use Sensor\Entity\PayloadEntity;
use Sensor\Model\Validated\Timestamp;

class PayloadRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function insert(PayloadEntity $payload): int {
        $sql = 'INSERT INTO event_payload
                (key, created, payload)
            VALUES
                (:key, :created, :payload)
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $payload->apiKeyId);
        $stmt->bindValue(':created', $payload->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':payload', $payload->payload);
        $stmt->execute();

        $result = $stmt->fetch();

        return $result['id'];
    }
}
