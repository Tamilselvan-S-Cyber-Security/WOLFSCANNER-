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

class EventEntity {
    public function __construct(
        public int $accountId,
        public SessionEntity $session,
        public int $apiKeyId,
        public IpAddressEntity|IpAddressEnrichedEntity|IpAddressLocalhostEnrichedEntity $ipAddress,
        public UrlEntity $url,
        public ?string $eventType,
        public ?string $httpMethod,
        public DeviceEntity $device,
        public ?RefererEntity $referer,
        public EmailEntity|EmailEnrichedEntity|null $email,
        public PhoneEntity|PhoneEnrichedEntity|PhoneInvalidEntity|null $phone,
        public ?int $httpCode,
        public \DateTimeImmutable $eventTime,
        public ?string $traceId,
        public ?PayloadEntity $payload,
        public ?FieldHistoryEntity $fieldHistory,
        public CountryEntity $country,
    ) {
    }
}
