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

/**
 * Free IP geolocation lookup using ip-api.com (no API key required).
 * Free tier allows up to 45 requests/minute on HTTP.
 */
class FreeGeoIp {
    private const API_URL    = 'http://ip-api.com/batch?fields=status,message,country,countryCode,query';
    private const FIELDS     = 'status,message,country,countryCode,query';
    public  const BATCH_SIZE = 40;

    /**
     * Look up country info for a batch of IP addresses.
     *
     * Returns an array indexed by IP address on success:
     *   [ '1.2.3.4' => ['countryCode' => 'US', 'country' => 'United States'], ... ]
     *
     * Returns null on a transport/network error (caller should retry later).
     * Returns [] when the API responded but resolved no IPs (e.g. all invalid).
     *
     * Entries whose lookup returned status != 'success' are omitted from the result.
     */
    public static function lookupBatch(array $ips): ?array {
        if (empty($ips)) {
            return [];
        }

        $ips = array_values(array_unique(array_map('strval', $ips)));
        $ips = array_slice($ips, 0, self::BATCH_SIZE);

        $payload = array_map(
            static fn(string $ip): array => ['query' => $ip, 'fields' => self::FIELDS],
            $ips
        );

        $body = json_encode($payload);
        if ($body === false) {
            return null;
        }

        $request = new \Wolf\Entities\HttpRequest(
            self::API_URL,
            'POST',
            ['Content-Type: application/json', 'Accept: application/json'],
            $body,
            5,
            15,
            false
        );

        $client   = \Wolf\Utils\Http\HttpClient::default();
        $response = $client->request($request);

        // Non-200 or no response body means a transport-level failure — signal retry
        if ($response->code() !== 200) {
            return null;
        }

        $rows = $response->body();
        if (!is_array($rows)) {
            return null;
        }

        $result = [];
        foreach ($rows as $row) {
            if (
                isset($row['query'], $row['status']) &&
                $row['status'] === 'success' &&
                !empty($row['countryCode'])
            ) {
                $result[$row['query']] = [
                    'countryCode' => strtoupper(trim($row['countryCode'])),
                    'country'     => isset($row['country']) ? (string) $row['country'] : '',
                ];
            }
        }

        return $result;
    }
}
