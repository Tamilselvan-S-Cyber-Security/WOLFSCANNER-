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

namespace Sensor\Model\Config;

class DatabaseConfig implements \JsonSerializable {
    public function __construct(
        public string $dbHost,
        public int $dbPort,
        public string $dbUser,
        #[\SensitiveParameter]
        public string $dbPassword,
        public string $dbDatabaseName,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        return [
            'dbHost' => $this->dbHost,
            'dbPort' => $this->dbPort,
            'dbUser' => $this->dbUser,
            'dbDatabaseName' => $this->dbDatabaseName,
        ];
    }
}
