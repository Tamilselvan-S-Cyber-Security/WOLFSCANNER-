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

class Network {
    public static function sendApiRequest(?array $data, string $path, string $method, ?string $enrichmentKey): \Wolf\Entities\HttpResponse {
        $version = \Wolf\Utils\VersionControl::versionString();
        $userAgent = \Base::instance()->get('APP_USER_AGENT');
        $userAgent = ($version && $userAgent) ? $userAgent . '/' . $version : $userAgent;

        $url = \Wolf\Utils\Variables::getEnrichmentApi() . $path;

        $headers = [
            'User-Agent: ' . $userAgent,
        ];

        if ($enrichmentKey !== null) {
            $headers[] = 'Authorization: Bearer ' . $enrichmentKey;
        }

        $body = null;
        if ($data !== null) {
            $body = json_encode($data);
            if ($body === false) {
                return \Wolf\Entities\HttpResponse::failure(null, 'json_encode_failed', []);
            }
        }

        $headers = \Wolf\Utils\Http\HeaderUtils::ensureHeader($headers, 'Content-Type', 'application/json');

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            $data = json_encode($data);
        }

        $request = new \Wolf\Entities\HttpRequest($url, $method, $headers, $data);
        $client = \Wolf\Utils\Http\HttpClient::default();

        return $client->request($request);
    }
}
