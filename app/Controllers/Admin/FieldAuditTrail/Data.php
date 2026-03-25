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

namespace Wolf\Controllers\Admin\FieldAuditTrail;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Wolf\Models\Grid\FieldAuditTrail\Grid($apiKey);

        $map = [
            'userId'        => 'getDataByUserId',
            'resourceId'    => 'getDataByResourceId',
            'fieldId'       => 'getDataByFieldId',
        ];

        $result = $this->idMapIterate($map, $model);

        $ids = array_column($result['data'], 'field_audit_id');
        if ($ids) {
            $model = new \Wolf\Models\FieldAudit();
            $model->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = $model->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getFieldEventDetails(int $id, int $apiKey): array {
        $result = [];
        $model = new \Wolf\Models\FieldAuditTrail();
        $trailResult = $model->getById($id, $apiKey);

        if ($trailResult) {
            $eventId = $trailResult['event_id'];
            $controller = new \Wolf\Controllers\Admin\Events\Data();
            $result = $controller->getEventDetails($eventId, $apiKey);

            if ($result) {
                $result = $controller->extendPayload($result, $apiKey);
            }
        }

        return $result;
    }
}
