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

namespace Sensor\Model\Validated;

class Phone extends Base {
    private const MAX_PHONE_LENGTH = 19;
    public string $value;

    public function __construct(string $value) {
        parent::__construct($value, 'phoneNumber');
        $value = str_replace(' ', '', $value);

        if (strlen($value) > self::MAX_PHONE_LENGTH) {
            $this->value = substr($value, 0, self::MAX_PHONE_LENGTH);
        } else {
            // even if empty string!
            $this->value = $value;
        }

        $this->invalid = strlen($value) > self::MAX_PHONE_LENGTH;
    }

    public function isEmpty(): bool {
        return $this->value === '';
    }
}
