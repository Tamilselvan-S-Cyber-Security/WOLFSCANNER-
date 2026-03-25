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

namespace Sensor\Entity;

class DomainEnrichedEntity {
    public function __construct(
        public int $apiKeyId,
        public string $domain,
        public bool $blockdomains,
        public bool $disposableDomains,
        public bool $freeEmailProvider,
        public ?string $ip,
        public ?string $geoIp,
        public ?string $geoHtml,
        public ?string $webServer,
        public ?string $hostname,
        public ?string $emails,
        public ?string $phone,
        public string $discoveryDate,
        public ?int $trancoRank,
        public ?string $creationDate,
        public ?string $expirationDate,
        public ?int $returnCode,
        public bool $disabled,
        public ?string $closestSnapshot,
        public bool $mxRecord,
        public bool $checked,
        public \DateTimeImmutable $lastSeen,
    ) {
    }
}
