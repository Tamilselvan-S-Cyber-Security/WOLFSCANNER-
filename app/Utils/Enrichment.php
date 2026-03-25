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

class Enrichment {
    public static function calculateIpType(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            $type = null;

            if ($record['fraud_detected']) {
                $type = 'Blacklisted';
            }
            if ($record['blocklist'] && !$type) {
                $type = 'Spam list';
            }
            if (($record['country_id'] === 0 || $record['country_id'] === null) && $record['checked'] && !$type) {
                $type = 'Localhost';
            }
            if ($record['tor'] && !$type) {
                $type = 'TOR';
            }
            if ($record['starlink'] && !$type) {
                $type = 'Starlink';
            }
            if ($record['relay'] && !$type) {
                $type = 'AppleRelay';
            }
            if ($record['vpn'] && !$type) {
                $type = 'VPN';
            }
            if ($record['data_center'] && !$type) {
                $type = 'Datacenter';
            }
            if (!$record['checked']) {
                $type = 'Unknown';
            }
            if (!$type) {
                $type = 'Residential';
            }

            unset($record['tor']);
            unset($record['starlink']);
            unset($record['relay']);
            unset($record['vpn']);
            unset($record['data_center']);

            $record['ip_type'] = $type;

            $records[$i] = $record;
        }
    }

    public static function calculateEmailReputation(array &$records, string $fieldName = 'reputation'): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];
            $reputation = 'none';

            if ($record['data_breach'] !== null) {
                $reputationLevel = \Wolf\Utils\Conversion::intVal($record['data_breach'], 0) + \Wolf\Utils\Conversion::intVal(!$record['blockemails'], 0);
                $reputation = match ($reputationLevel) {
                    2       => 'high',
                    1       => 'medium',
                    0       => 'low',
                    default => 'none',
                };
            }

            /*if (!$record['profiles'] && !$record['data_breach'] && $record['blockemails']) {
                $reputation = 'low';
            } elseif (!$record['profiles'] && $record['data_breach'] && !$reputation) {
                $reputation = 'medium';
            } elseif ($record['profiles'] && !$record['data_breach'] && !$reputation) {
                $reputation = 'medium';
            } elseif ($record['profiles'] && $record['data_breach'] && !$reputation) {
                $reputation = 'high';
            } else {
                $reputation = 'none';
            }*/

            $record[$fieldName] = $reputation;

            $records[$i] = $record;
        }
    }

    public static function calculateEmailReputationForContext(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            //$record['profiles'] = $record['ee_profiles'] ?? 0;
            $record['data_breach'] = $record['ee_data_breach'] ?? false;
            $record['blockemails'] = $record['ee_blockemails'] ?? false;
            //$record['disposable_domains'] = $record['ed_disposable_domains'] ?? false;

            $records[$i] = $record;
        }

        $fieldName = 'ee_reputation';
        self::calculateEmailReputation($records, $fieldName);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            //unset($record['profiles']);
            unset($record['data_breach']);
            unset($record['blockemails']);
            //unset($record['disposable_domains']);

            $records[$i] = $record;
        }
    }

    public static function applyDeviceParams(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            $device = $record['device'] ?? 'unknown';
            $browserName = $record['browser_name'] ?? '';
            $browserVersion = $record['browser_version'] ?? '';
            $osName = $record['os_name'] ?? '';
            $osVersion = $record['os_version'] ?? '';

            //Display 'Bot' label instead of his full name
            //$record['os_name'] = $device === 'bot' ? 'Bot' : $osName;

            // Windows 11 often still reports "Windows NT 10.0" in classic UA, which
            // makes many detectors return os_version "10".
            // We correct only when the payload provides an explicit Windows 11 signal:
            //   - Windows-11 / Windows/11 / Windows 11 markers in UA text
            //   - optional client-hint platform version >= 13 (if available in record)
            if ($osName === 'Windows' && $osVersion === '10') {
                $ua = $record['ua'] ?? '';
                $platformVersion = isset($record['ua_platform_version']) ? (string) $record['ua_platform_version'] : '';

                $windows11ByUa = $ua !== '' && preg_match('/Windows(?:[-\/\s])11(?!\d)/i', $ua);
                $windows11ByHint = $platformVersion !== '' && version_compare($platformVersion, '13.0.0', '>=');

                if ($windows11ByUa || $windows11ByHint) {
                    $osVersion = '11';
                }
            }

            $record['os'] = sprintf('%s %s', $osName, $osVersion);
            $record['browser'] = sprintf('%s %s', $browserName, $browserVersion);
            $record['device_name'] = $device;

            $records[$i] = $record;
        }
    }
}
