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

class CountryRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function getCountryIdByCode(string $code): int {
        $sql = 'SELECT id FROM countries WHERE iso = :iso LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':iso', $code);
        $stmt->execute();

        $result = $stmt->fetchColumn();

        return intval($result);
    }
}
