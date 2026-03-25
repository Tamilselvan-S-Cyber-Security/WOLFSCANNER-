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

namespace Wolf\Controllers\Admin\ManualCheck;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return $this->performSearch();
    }

    public function performSearch(): array {
        $params = $this->extractRequestParams(['token', 'search', 'type']);

        $pageParams = [
            'SEARCH_VALUES' => $params,
        ];

        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $enrichmentKey = \Wolf\Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();
        $errorCode = \Wolf\Utils\Validators::validateSearch($params, $enrichmentKey);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;

            return $pageParams;
        }

        $type   = \Wolf\Utils\Conversion::getStringRequestParam('type');
        $search = \Wolf\Utils\Conversion::getStringRequestParam('search');

        $controller = new \Wolf\Controllers\Admin\Enrichment\Data();
        $result = $controller->enrichEntity($type, $search, null, $apiKey, $enrichmentKey);

        if (isset($result['ERROR_CODE'])) {
            $pageParams['ERROR_CODE'] = $result['ERROR_CODE'];

            return $pageParams;
        }

        $operatorId = \Wolf\Utils\Routes::getCurrentRequestOperator()->id;
        $this->saveSearch($search, $type, $operatorId);

        // TODO: return alert_list back in next release
        if (array_key_exists('alert_list', $result[$type])) {
            unset($result[$type]['alert_list']);
        }

        if ($type === 'phone') {
            unset($result[$type]['valid']);
            unset($result[$type]['validation_error']);
        }

        if ($type === 'email') {
            unset($result[$type]['data_breaches']);
        }

        $pageParams['RESULT'] = [$type => $result[$type]];

        return $pageParams;
    }

    private function saveSearch(string $query, string $type, int $operatorId): void {
        $history = new \Wolf\Models\ManualCheckHistory();
        $history->insertRecord($query, $type, $operatorId);
    }

    public function getSearchHistory(int $operatorId): ?array {
        $model = new \Wolf\Models\ManualCheckHistory();

        return $model->getLastByOperatorId($operatorId);
    }
}
