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

namespace Sensor\Service;

use Sensor\Model\Blacklist\FraudDetected;
use Sensor\Repository\BlacklistRepository;
use Sensor\Type\BlacklistType;

class FraudDetectionService {
    public function __construct(
        private BlacklistRepository $blacklistRepository,
    ) {
    }

    public function getEarlierDetectedFraud(
        int $apiKeyId,
        ?string $emailAddress,
        string $ipAddress,
        ?string $phoneNumber,
    ): FraudDetected {
        $ipBlacklisted = $this->blacklistRepository->isBlacklisted($apiKeyId, BlacklistType::Ip, $ipAddress);
        if ($emailAddress !== null) {
            $emailBlacklisted = $this->blacklistRepository->isBlacklisted($apiKeyId, BlacklistType::Email, $emailAddress);
        }
        if ($phoneNumber !== null) {
            $phoneBlacklisted = $this->blacklistRepository->isBlacklisted($apiKeyId, BlacklistType::Phone, $phoneNumber);
        }

        return new FraudDetected(
            $emailBlacklisted ?? false,
            $ipBlacklisted,
            $phoneBlacklisted ?? false,
        );
    }
}
