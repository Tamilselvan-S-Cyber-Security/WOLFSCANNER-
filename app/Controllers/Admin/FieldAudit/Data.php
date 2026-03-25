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

namespace Wolf\Controllers\Admin\FieldAudit;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function checkIfOperatorHasAccess(int $fieldId): bool {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $model = new \Wolf\Models\FieldAudit();

        return $model->checkAccess($fieldId, $apiKey);
    }

    public function getFieldById(int $fieldId): array {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();

        $model = new \Wolf\Models\FieldAudit();
        $result = $model->getFieldById($fieldId, $apiKey);
        $result['lastseen'] = \Wolf\Utils\ElapsedDate::short($result['lastseen']);
        $result['created'] = \Wolf\Utils\ElapsedDate::short($result['created']);

        return $result;
    }
}
