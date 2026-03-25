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

namespace Wolf\Models;

class Ip extends \Wolf\Models\BaseSql implements \Wolf\Interfaces\ApiKeyAccessAuthorizationInterface, \Wolf\Interfaces\FraudFlagUpdaterInterface {
    protected ?string $DB_TABLE_NAME = 'event_ip';

    public function getIdByValue(string $ipAddress, int $apiKey): ?int {
        $params = [
            ':ip_value' => $ipAddress,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_ip.id
            FROM
                event_ip
            WHERE
                event_ip.ip = :ip_value AND
                event_ip.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? null;
    }

    public function getFullIpInfoById(int $ipId, int $apiKey): array {
        $params = [
            ':ipid'     => $ipId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                event_ip.id,
                event_ip.ip,
                event_ip.cidr,
                event_ip.lastseen,
                event_ip.created,
                event_ip.ip AS title,
                event_ip.isp AS ispid,
                event_ip.data_center,
                event_ip.relay,
                event_ip.starlink,
                event_ip.vpn,
                event_ip.tor,
                event_ip.fraud_detected,
                event_ip.blocklist,
                event_ip.checked,

                event_isp.asn,
                event_isp.name,
                event_isp.description,

                countries.value AS full_country,
                countries.id    AS country_id,
                countries.iso   AS country_iso

            FROM
                event_ip

            LEFT JOIN event_isp
            ON (event_ip.isp = event_isp.id)

            LEFT JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event_ip.id = :ipid AND
                event_ip.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':ip_id' => $subjectId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_ip.id

            FROM
                event_ip

            WHERE
                event_ip.id = :ip_id AND
                event_ip.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function updateFraudFlag(array $ids, bool $fraud, int $apiKey): void {
        if (!count($ids)) {
            return;
        }

        [$params, $placeHolders] = $this->getArrayPlaceholders($ids);

        $params[':fraud'] = $fraud;
        $params[':api_key'] = $apiKey;

        $query = (
            "UPDATE event_ip
                SET fraud_detected = :fraud

            WHERE
                id IN ({$placeHolders}) AND
                key = :api_key"
        );

        $this->execQuery($query, $params);
    }

    public function extractById(int $entityId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $entityId,
        ];

        $query = (
            "SELECT
                split_part(COALESCE(event_ip.ip::text, ''), '/', 1) AS value,
                event_ip.hash AS hash

            FROM
                event_ip

            WHERE
                event_ip.id = :id AND
                event_ip.key = :api_key

            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event.ip AS id,
                COUNT(*) AS cnt
            FROM event
            WHERE
                event.ip IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event.ip"
        );

        $totalVisit = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = ['total_visit' => 0];
        }

        foreach ($totalVisit as $rec) {
            $result[$rec['id']]['total_visit'] = $rec['cnt'];
        }

        return $result;
    }

    public function updateTotalsByEntityIds(array $ids, int $apiKey, bool $force = false): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $extraClause = $force ? '' : ' AND event_ip.lastseen >= event_ip.updated';

        $query = (
            "UPDATE event_ip
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                shared = COALESCE(sub.shared, 1),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event.ip,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS shared
                FROM event
                WHERE
                    event.ip IN ($flatIds) AND
                    event.key = :key
                GROUP BY event.ip
            ) AS sub
            RIGHT JOIN event_ip sub_ip ON sub.ip = sub_ip.id
            WHERE
                event_ip.id = sub_ip.id AND
                event_ip.id IN ($flatIds) AND
                event_ip.key = :key
                $extraClause"
        );

        $this->execQuery($query, $params);
    }

    public function updateTotalsByAccountIds(array $ids, int $apiKey): int {
        if (!count($ids)) {
            return 0;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;

        $idsQuery = (
            "SELECT
                DISTINCT event.ip
            FROM event
            WHERE
                event.account IN ($flatIds) AND
                event.key = :key"
        );

        $query = (
            "UPDATE event_ip
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                shared = COALESCE(sub.shared, 1),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event.ip,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS shared
                FROM event
                WHERE
                    event.ip IN ($idsQuery) AND
                    event.key = :key
                GROUP BY event.ip
            ) AS sub
            RIGHT JOIN event_ip sub_ip ON sub.ip = sub_ip.id
            WHERE
                event_ip.id = sub.ip AND
                event_ip.id IN ($idsQuery) AND
                event_ip.key = :key AND
                event_ip.lastseen >= event_ip.updated"
        );

        return $this->execQuery($query, $params);
    }

    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                id,
                total_visit,
                shared AS total_account
            FROM event_ip
            WHERE id IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);
        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_visit'] = $indexedResult[$item['id']]['total_visit'];
            $item['total_account'] = $indexedResult[$item['id']]['total_account'];
            $res[$idx] = $item;
        }

        return $res;
    }

    public function countNotChecked(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        // count only ips appearing in events (not overriden by retention)
        $query = (
            'SELECT
                COUNT(DISTINCT event_ip.id) AS count
            FROM event
            LEFT JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.key = :key AND
                event_ip.checked IS FALSE'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function notCheckedExists(int $apiKey): bool {
        $params = [
            ':key' => $apiKey,
        ];

        // count only ips appearing in events (not overriden by retention)
        $query = (
            'SELECT 1
            FROM event
            LEFT JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.key = :key AND
                event_ip.checked IS FALSE
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return (bool) count($results);
    }

    public function notCheckedForUserId(int $userId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_id' => $userId,
        ];

        $query = (
            'SELECT DISTINCT
                event_ip.id
            FROM event
            LEFT JOIN event_ip ON event.ip = event_ip.id
            WHERE
                event.account = :user_id AND
                event.key = :api_key AND
                event_ip.checked IS FALSE'
        );

        return array_column($this->execQuery($query, $params), 'id');
    }

    /**
     * Returns distinct public IPv4 addresses (country = 0, not yet enriched)
     * that still need a country assignment, along with one representative API
     * key and lastseen timestamp per IP.
     *
     * Uses host() to return a plain IP string (no CIDR suffix) so it can be
     * sent directly to the ip-api.com batch endpoint.
     */
    public function getIpsNeedingCountry(int $limit): array {
        $query = sprintf(
            'SELECT DISTINCT ON (event_ip.ip)
                host(event_ip.ip) AS ip,
                event_ip.key,
                event_ip.lastseen
            FROM event_ip
            WHERE
                (event_ip.country = 0 OR event_ip.country IS NULL) AND
                event_ip.checked IS FALSE AND
                family(event_ip.ip) = 4 AND
                NOT (event_ip.ip << \'10.0.0.0/8\'::cidr) AND
                NOT (event_ip.ip << \'172.16.0.0/12\'::cidr) AND
                NOT (event_ip.ip << \'192.168.0.0/16\'::cidr) AND
                NOT (event_ip.ip << \'127.0.0.0/8\'::cidr) AND
                NOT (event_ip.ip << \'169.254.0.0/16\'::cidr)
            ORDER BY event_ip.ip, event_ip.lastseen DESC
            LIMIT %d',
            $limit
        );

        return $this->execQuery($query, null) ?: [];
    }

    /**
     * Returns all API-key scoped rows for the provided IPs that still have an
     * unknown country (country = 0), grouped by ip+key with the latest
     * lastseen timestamp per group.
     *
     * @param string[] $ips Plain IP address strings (no CIDR suffix).
     *
     * @return array<int, array{ip: string, key: int|string, lastseen: string}>
     */
    public function getPendingCountryRowsByIps(array $ips): array {
        if (empty($ips)) {
            return [];
        }

        [$params, $placeholders] = $this->getArrayPlaceholders($ips);

        $query = sprintf(
            'SELECT
                host(event_ip.ip) AS ip,
                event_ip.key,
                MAX(event_ip.lastseen) AS lastseen
            FROM event_ip
            WHERE
                host(event_ip.ip) IN (%s) AND
                (event_ip.country = 0 OR event_ip.country IS NULL)
            GROUP BY host(event_ip.ip), event_ip.key',
            $placeholders
        );

        return $this->execQuery($query, $params) ?: [];
    }

    /**
     * Assigns a resolved country to every event_ip row with the given IP
     * address that still has country = 0 (unknown).
     */
    public function updateCountryForIp(string $ip, int $countryId): void {
        $params = [
            ':country' => $countryId,
            ':ip'      => $ip,
        ];

        $query = (
            'UPDATE event_ip
            SET country = :country
            WHERE
                ip = :ip::inet AND
                (country = 0 OR country IS NULL)'
        );

        $this->execQuery($query, $params);
    }

    /**
     * Marks all event_ip rows for the given IPs as checked = true so they are
     * no longer retried by the free geo-IP cron when the API cannot resolve them.
     *
     * @param string[] $ips Plain IP address strings (no CIDR suffix).
     */
    public function markIpsAsChecked(array $ips): void {
        if (empty($ips)) {
            return;
        }

        [$params, $placeholders] = $this->getArrayPlaceholders($ips);

        $query = sprintf(
            'UPDATE event_ip
            SET checked = TRUE
            WHERE host(ip) IN (%s)',
            $placeholders
        );

        $this->execQuery($query, $params);
    }

    /**
     * Marks private, loopback and link-local IPs as checked = TRUE so they
     * are never sent to the geo-IP API and are correctly surfaced as
     * 'Localhost' by Enrichment::calculateIpType().
     *
     * Covers RFC-1918 private ranges, loopback, link-local for both IPv4 and
     * the common IPv6 equivalents.
     *
     * @return int Number of rows updated.
     */
    public function markPrivateIpsAsChecked(): int {
        $query = (
            'UPDATE event_ip
            SET checked = TRUE
            WHERE
                (country = 0 OR country IS NULL) AND
                checked IS FALSE AND
                (
                    (
                        family(ip) = 4 AND
                        (
                            ip << \'10.0.0.0/8\'::cidr      OR
                            ip << \'172.16.0.0/12\'::cidr   OR
                            ip << \'192.168.0.0/16\'::cidr  OR
                            ip << \'127.0.0.0/8\'::cidr     OR
                            ip << \'169.254.0.0/16\'::cidr
                        )
                    ) OR
                    (
                        family(ip) = 6 AND
                        (
                            ip << \'::1/128\'::cidr         OR
                            ip << \'fc00::/7\'::cidr        OR
                            ip << \'fe80::/10\'::cidr
                        )
                    )
                )'
        );

        $result = $this->execQuery($query, null);

        return is_array($result) ? 0 : (int) $result;
    }
}
