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

namespace Wolf\Controllers\Api;

class Blacklist extends Endpoint {
    public function search(): void {
        $value = $this->getBodyProp('value', 'string');

        $model = new \Wolf\Models\BlacklistItems();
        $itemFound = $model->searchBlacklistedItem($value, $this->apiKeyId);

        $this->data = [
            'value'         => $value,
            'blacklisted'   => $itemFound,
        ];
    }
}
