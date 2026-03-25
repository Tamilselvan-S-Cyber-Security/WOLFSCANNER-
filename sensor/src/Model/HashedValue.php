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

namespace Sensor\Model;

use Sensor\Model\Validated\Email;
use Sensor\Model\Validated\IpAddress;
use Sensor\Model\Validated\Phone;

class HashedValue {
    public string $value;
    public string $hash;
    public ?bool $localhost = null;

    public function __construct(
        Email|IpAddress|Phone $input,
    ) {
        $this->value = $input->value;
        $this->hash = hash('sha256', $this->value);
        if ($input instanceof IpAddress) {
            $this->localhost = $input->isLocalhost();
        }
    }

    /**
     * @return array{value: string, hash: ?string}
     */
    public function toArray(bool $hashExchange): array {
        return [
            'value' => $this->value,
            'hash' => $hashExchange ? $this->hash : null,
        ];
    }
}
