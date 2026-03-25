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

namespace Wolf\Utils;

class ApiKeys {
    public static function getCurrentOperatorApiKeyId(): ?int {
        $key = \Wolf\Utils\Routes::getCurrentRequestApiKey();

        return $key ? $key->id : null;
    }

    public static function getCurrentOperatorApiKeyString(): ?string {
        $key = \Wolf\Utils\Routes::getCurrentRequestApiKey();

        return $key ? $key->key : null;
    }

    public static function getCurrentOperatorEnrichmentKeyString(): ?string {
        $key = \Wolf\Utils\Routes::getCurrentRequestApiKey();

        return $key ? $key->token : null;
    }

    public static function getOperatorApiKeys(int $operatorId): array {
        $model = new \Wolf\Models\ApiKeys();
        $apiKeys = $model->getKeys($operatorId);

        $isOwner = true;
        if (!$apiKeys) {
            $coOwnerModel = new \Wolf\Models\ApiKeyCoOwner();
            $keyId = $coOwnerModel->getCoOwnershipKeyId($operatorId);

            if ($keyId) {
                $isOwner = false;
                $apiKeys[] = $model->getKeyById($keyId);
            }
        }

        return [$isOwner, $apiKeys];
    }

    public static function getFirstKeyByOperatorId(int $operatorId): ?int {
        $model = new \Wolf\Models\ApiKeys();
        $apiKeys = $model->getKeys($operatorId);

        if (!$apiKeys) {
            $coOwnerModel = new \Wolf\Models\ApiKeyCoOwner();
            $keyId = $coOwnerModel->getCoOwnershipKeyId($operatorId);

            if ($keyId) {
                $apiKeys[] = $model->getKeyById($keyId);
            }
        }

        return $apiKeys[0]['id'] ?? null;
    }
}
