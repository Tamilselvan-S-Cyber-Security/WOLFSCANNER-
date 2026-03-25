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

namespace Wolf\Controllers\Admin\Totals;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function getTimeFrameTotal(array $ids, string $type, string $startDate, string $endDate, int $apiKey): array {
        $processErrorMessage = ['ERROR_CODE' => \Wolf\Utils\ErrorCodes::TOTALS_INVALID_TYPE];

        if (!in_array($type, ['ip', 'isp', 'domain', 'country', 'resource', 'field', 'userAgent'])) {
            return $processErrorMessage;
        }

        $model = null;

        switch ($type) {
            case 'ip':
                $model = new \Wolf\Models\Ip();
                break;
            case 'isp':
                $model = new \Wolf\Models\Isp();
                break;
            case 'domain':
                $model = new \Wolf\Models\Domain();
                break;
            case 'country':
                $model = new \Wolf\Models\Country();
                break;
            case 'resource':
                $model = new \Wolf\Models\Resource();
                break;
            case 'field':
                $model = new \Wolf\Models\FieldAudit();
                break;
            case 'userAgent':
                $model = new \Wolf\Models\UserAgent();
                break;
        }

        $totals = $model->getTimeFrameTotal($ids, $startDate, $endDate, $apiKey);

        return [
            'SUCCESS_MESSAGE'   => $this->f3->get('AdminTotals_success_message'),
            'totals'            => $totals,
        ];
    }
}
