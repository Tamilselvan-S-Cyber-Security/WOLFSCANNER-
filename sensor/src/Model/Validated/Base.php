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

class Base {
    private string $type;
    public bool $invalid;
    public string $origin;

    public function __construct(string $value, string $type) {
        $this->origin = $value;
        $this->type = $type;
    }

    public function validationStatement(): ?string {
        if ($this->invalid) {
            return "$this->type validation error on `$this->origin`";
        }

        return null;
    }
}
