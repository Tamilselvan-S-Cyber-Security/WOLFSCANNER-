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

namespace Wolf\Controllers\Admin\ISP;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function checkIfOperatorHasAccess(int $ispId, int $apiKey): bool {
        return (new \Wolf\Models\Isp())->checkAccess($ispId, $apiKey);
    }

    public function getFullIspInfoById(int $ispId, int $apiKey): array {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $model = new \Wolf\Models\Isp();
        $result = $model->getFullIspInfoById($ispId, $apiKey);
        $result['lastseen'] = \Wolf\Utils\ElapsedDate::short($result['lastseen']);

        return $result;
    }

    private function getNumberOfIpsByIspId(int $ispId, int $apiKey): int {
        return (new \Wolf\Models\Isp())->getIpCountById($ispId, $apiKey);
    }

    public function getIspDetails(int $ispId, int $apiKey): array {
        $result = [];
        $data = $this->getFullIspInfoById($ispId, $apiKey);

        if (array_key_exists('asn', $data)) {
            $result = [
                'asn'           => $data['asn'],
                'total_fraud'   => $data['total_fraud'],
                'total_visit'   => $data['total_visit'],
                'total_account' => $data['total_account'],
                'total_ip'      => $this->getNumberOfIpsByIspId($ispId, $apiKey),
            ];
        }

        return $result;
    }
}
