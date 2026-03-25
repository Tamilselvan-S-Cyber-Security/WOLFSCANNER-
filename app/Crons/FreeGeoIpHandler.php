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

namespace Wolf\Crons;

/**
 * Fills in missing country data for IPs that have not yet been enriched
 * by the paid API, using ip-api.com (free, no API key required).
 *
 * Runs every minute and processes up to 40 IPs per run to stay within
 * ip-api.com's free-tier rate limit of 45 requests/minute.
 */
class FreeGeoIpHandler extends \Wolf\Crons\Base {
    public function process(): void {
        $ipModel      = new \Wolf\Models\Ip();
        $countryModel = new \Wolf\Models\Country();

        // Mark private/loopback IPs as checked so they are surfaced as
        // 'Localhost' by the enrichment layer and are never sent to the API.
        $ipModel->markPrivateIpsAsChecked();

        $rows = $ipModel->getIpsNeedingCountry(\Wolf\Utils\FreeGeoIp::BATCH_SIZE);

        if (empty($rows)) {
            $this->addLog('No IPs require free geo-location.');
            return;
        }

        $ipValues = array_column($rows, 'ip');
        $this->addLog(sprintf('Looking up %d IPs via ip-api.com.', count($ipValues)));

        // Collect every api-key scoped pending row for the selected IPs so
        // event_country can be updated consistently across keys.
        $pendingRows = $ipModel->getPendingCountryRowsByIps($ipValues);
        $rowsByIp = [];
        foreach ($pendingRows as $row) {
            $rowsByIp[$row['ip']][] = $row;
        }

        $geoData = \Wolf\Utils\FreeGeoIp::lookupBatch($ipValues);

        if ($geoData === null) {
            // null means a network/transport error — do not mark IPs as checked so they retry
            $this->addLog('ip-api.com request failed (network error). Will retry next run.');
            return;
        }

        if (empty($geoData)) {
            // Empty but not null — API responded but resolved nothing (e.g. all private/invalid)
            // Mark all attempted IPs as checked to stop infinite retry
            $ipModel->markIpsAsChecked($ipValues);
            $this->addLog('ip-api.com returned no successful results. IPs marked as checked.');
            return;
        }

        // Collect IPs the API could NOT resolve so we can mark them as checked
        $resolvedIps = array_keys($geoData);
        $unresolvedIps = array_values(array_diff($ipValues, $resolvedIps));

        $updated = 0;
        $failed  = 0;

        foreach ($geoData as $ip => $geo) {
            $iso = $geo['countryCode'] ?? '';

            if (strlen($iso) !== 2) {
                $failed++;
                $unresolvedIps[] = $ip;
                continue;
            }

            $countryId = $countryModel->getCountryIdByIso($iso);

            if (!$countryId) {
                $this->addLog(sprintf('Country ISO "%s" not found in countries table for IP %s.', $iso, $ip));
                $failed++;
                // Do not mark as checked here. Keep retriable so once countries
                // table is fixed/updated, this IP can resolve automatically.
                continue;
            }

            // Update event_ip rows for this IP address across all API keys
            $ipModel->updateCountryForIp($ip, $countryId);

            // Upsert event_country for every key that has this IP pending.
            // insertRecord() already does ON CONFLICT ... DO UPDATE lastseen.
            if (isset($rowsByIp[$ip])) {
                foreach ($rowsByIp[$ip] as $ownerRow) {
                    $countryModel->insertRecord([
                        'id'       => $countryId,
                        'lastseen' => $ownerRow['lastseen'],
                    ], (int) $ownerRow['key']);
                }
            }

            $updated++;
        }

        // Mark permanently-failed IPs as checked so they are not retried
        if (!empty($unresolvedIps)) {
            $ipModel->markIpsAsChecked(array_unique($unresolvedIps));
        }

        $this->addLog(sprintf(
            'FreeGeoIp complete: %d updated, %d failed/skipped out of %d IPs.',
            $updated,
            $failed,
            count($ipValues)
        ));
    }
}
