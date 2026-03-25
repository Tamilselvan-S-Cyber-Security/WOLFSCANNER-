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

class Cron {
    private const NOTIFICATION_WINDOW_HOUR_START = 9;
    private const NOTIFICATION_WINDOW_HOUR_END = 17;

    public static function getHashes(array $items, string $userEmail): array {
        $userHash = hash('sha256', $userEmail);

        return array_map(function ($item) use ($userHash) {
            return [
                'type'  => $item['type'],
                'value' => hash('sha256', $item['value']),
                'id'    => $userHash,
            ];
        }, $items);
    }

    public static function sendBlacklistReportPostRequest(array $hashes, string $enrichmentKey): string {
        $postFields = [
            'data' => $hashes,
        ];

        $response = \Wolf\Utils\Network::sendApiRequest($postFields, '/global_alert_report', 'POST', $enrichmentKey);

        return $response->error() ?? '';
    }

    public static function checkTimezone(string $timezone): bool {
        $hour = (new \DateTime('now', \Wolf\Utils\Timezones::getTimezone($timezone)))->format('H');
        $hour = \Wolf\Utils\Conversion::intValCheckEmpty($hour, 0);

        return $hour >= self::NOTIFICATION_WINDOW_HOUR_START && $hour < self::NOTIFICATION_WINDOW_HOUR_END;
    }

    public static function sendUnreviewedItemsReminderEmail(string $name, string $email, int $reviewCount): bool {
        $audit = \Audit::instance();
        if (!$audit->email($email, true)) {
            return false;
        }

        $subject = \Base::instance()->get('UnreviewedItemsReminder_email_subject');
        $subject = sprintf($subject, $reviewCount);

        $message = \Base::instance()->get('UnreviewedItemsReminder_email_body');
        $url = \Wolf\Utils\Variables::getHostWithProtocolAndBase();
        $message = sprintf($message, $name, $email, $reviewCount, $url);

        \Wolf\Utils\Mailer::send($name, $email, $subject, $message);

        return true;
    }

    public static function printLogs(array $logs): void {
        foreach ($logs as $log) {
            echo $log;
        }
    }
}
