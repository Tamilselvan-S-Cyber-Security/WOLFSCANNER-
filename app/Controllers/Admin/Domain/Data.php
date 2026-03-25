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

namespace Wolf\Controllers\Admin\Domain;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return match (\Wolf\Utils\Conversion::getStringRequestParam('cmd')) {
            'reenrichment' => $this->enrichEntity(),
            default => []
        };
    }

    public function enrichEntity(): array {
        $dataController = new \Wolf\Controllers\Admin\Enrichment\Data();
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $enrichmentKey = \Wolf\Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();

        $type       = \Wolf\Utils\Conversion::getStringRequestParam('type');
        $search     = \Wolf\Utils\Conversion::getStringRequestParam('search', true);
        $entityId   = \Wolf\Utils\Conversion::getIntRequestParam('entityId', true);

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function checkIfOperatorHasAccess(int $domainId, int $apiKey): bool {
        return (new \Wolf\Models\Domain())->checkAccess($domainId, $apiKey);
    }

    public function getDomainDetails(int $domainId, int $apiKey): array {
        $result = (new \Wolf\Models\Domain())->getFullDomainInfoById($domainId, $apiKey);

        $tsColumns = ['lastseen'];
        \Wolf\Utils\Timezones::localizeTimestampsForActiveOperator($tsColumns, $result);

        return $result;
    }

    public function isEnrichable(int $apiKey): bool {
        return (new \Wolf\Models\ApiKeys())->attributeIsEnrichable('domain', $apiKey);
    }
}
