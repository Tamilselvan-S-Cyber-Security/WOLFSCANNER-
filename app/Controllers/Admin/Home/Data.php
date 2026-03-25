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

namespace Wolf\Controllers\Admin\Home;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function getChart(string $mode, int $apiKey): array {
        $modelMap = \Wolf\Utils\Constants::get()->CHART_MODEL_MAP;

        $model = array_key_exists($mode, $modelMap) ? new $modelMap[$mode]() : null;

        return $model ? $model->getData($apiKey) : [[], []];
    }

    public function getStat(string $mode, ?array $dateRange, int $apiKey): array {
        $model = new \Wolf\Models\Dashboard();

        $result = [
            'total'         => null,
            'allTimeTotal'  => null,
        ];

        switch ($mode) {
            case 'totalEvents':
                $result['total']        = $model->getTotalEvents($dateRange, $apiKey);
                //$result['allTimeTotal'] = $model->getTotalEvents(null, $apiKey);
                break;
            case 'totalUsers':
                $result['total']        = $model->getTotalUsers($dateRange, $apiKey);
                //$result['allTimeTotal'] = $model->getTotalUsers(null, $apiKey);
                break;
            case 'totalIps':
                $result['total']        = $model->getTotalIps($dateRange, $apiKey);
                //$result['allTimeTotal'] = $model->getTotalIps(null, $apiKey);
                break;
            case 'totalCountries':
                $result['total']        = $model->getTotalCountries($dateRange, $apiKey);
                //$result['allTimeTotal'] = $model->getTotalCountries(null, $apiKey);
                break;
            case 'totalUrls':
                $result['total']        = $model->getTotalResources($dateRange, $apiKey);
                //$result['allTimeTotal'] = $model->getTotalResources(null, $apiKey);
                break;
            case 'totalUsersForReview':
                $result['total']        = $model->getTotalUsersForReview($dateRange, $apiKey);
                //$result['allTimeTotal'] = $model->getTotalUsersForReview(null, $apiKey);
                break;
            case 'totalBlockedUsers':
                $result['total']        = $model->getTotalBlockedUsers($dateRange, $apiKey);
                //$result['allTimeTotal'] = $model->getTotalBlockedUsers(null, $apiKey);
                break;
        }

        return $result;
    }

    public function getTopTen(string $mode, ?array $dateRange, int $apiKey): array {
        $modelMap = \Wolf\Utils\Constants::get()->TOP_TEN_MODELS_MAP;

        $model = array_key_exists($mode, $modelMap) ? new $modelMap[$mode]() : null;
        $data = $model ? $model->getList($apiKey, $dateRange) : [];
        $total = count($data);

        return [
            'draw'              => $this->f3->get('REQUEST.draw') ?? 1,
            'recordsTotal'      => $total,
            'recordsFiltered'   => $total,
            'data'              => $data,
        ];
    }

    public function getCurrentTime(\Wolf\Entities\Operator $operator): array {
        $offset = \Wolf\Utils\Timezones::getOperatorOffset($operator);
        $now = time() + $offset;
        $day = \Wolf\Utils\Constants::get()->SECONDS_IN_DAY;
        $firstJan = mktime(0, 0, 0, 1, 1, intval(gmdate('Y')));

        $day = \Wolf\Utils\Conversion::intVal(ceil(($now - $firstJan) / $day), 0);

        return [
            'clock_offset'      => $offset,
            'clock_day'         => ($day < 10 ? '00' : ($day < 100 ? '0' : '')) . strval($day),
            'clock_time_his'    => date('H:i:s', $now),
            'clock_timezone'    => 'UTC' . (($offset < 0) ? '-' . date('H:i', -$offset) : '+' . date('H:i', $offset)),
        ];
    }

    public function getConstants(): array {
        $constants = \Wolf\Utils\Assets\ConstantsClass::getConstantsObj();

        return $constants ? $constants::listConstants() : [];
    }
}
