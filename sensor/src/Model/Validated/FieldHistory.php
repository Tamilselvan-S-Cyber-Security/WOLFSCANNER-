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

class FieldHistory extends BaseArray {
    public function __construct(mixed $value) {
        $this->requiredFields = [
            'field_id',
            'new_value',
        ];

        $this->optionalFields = [
            'field_name',
            'old_value',
            'parent_id',
            'parent_name',
        ];

        $this->set = true;
        $this->dump = false;

        parent::__construct($value, 'fieldHistory');
    }
}
