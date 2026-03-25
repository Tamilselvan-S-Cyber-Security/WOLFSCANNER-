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

namespace Wolf\Controllers\Admin\Phones;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Wolf\Models\Grid\Phones\Grid($apiKey);

        $map = [
            'userId' => 'getPhonesByUserId',
        ];

        $result = $this->idMapIterate($map, $model, null);

        $ids = array_column($result['data'], 'id');
        if ($ids) {
            $model = new \Wolf\Models\Phone();
            $model->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = $model->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getPhoneDetails(int $id, int $apiKey): array {
        $details = (new \Wolf\Models\Phone())->getPhoneDetails($id, $apiKey);
        $details['enrichable'] = $this->isEnrichable($apiKey);

        $tsColumns = ['created', 'lastseen'];
        \Wolf\Utils\Timezones::localizeTimestampsForActiveOperator($tsColumns, $details);

        return $details;
    }

    private function isEnrichable(int $apiKey): bool {
        return (new \Wolf\Models\ApiKeys())->attributeIsEnrichable('phone', $apiKey);
    }
}
