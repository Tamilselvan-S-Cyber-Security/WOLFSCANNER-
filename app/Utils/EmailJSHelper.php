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
 * EmailJS Helper Class
 * Prepares security alert data for EmailJS integration
 */
class EmailJSHelper
{
    /**
     * EmailJS Configuration
     */
    private const SERVICE_ID = 'service_dh7bik6';
    private const TEMPLATE_ID = 'template_nb3q8or';
    private const PUBLIC_KEY = '5cU_oY5m7HtS2WiX4';

    /**
     * Prepare security alert data for EmailJS
     *
     * @param array $data Alert data
     * @return array Formatted data for EmailJS
     */
    public static function prepareSecurityAlert(array $data): array
    {
        return [
            // Alert Configuration
            'alert_type' => $data['alert_type'] ?? 'info',
            'alert_message' => $data['alert_message'] ?? 'Security event detected',
            'event_type' => $data['event_type'] ?? 'Unknown Event',
            'risk_level' => $data['risk_level'] ?? 'medium',
            'timestamp' => $data['timestamp'] ?? date('Y-m-d H:i:s'),
            'detection_method' => $data['detection_method'] ?? 'Auto Scanner',

            // User Information
            'user_id' => $data['user_id'] ?? 'N/A',
            'username' => $data['username'] ?? 'Anonymous',
            'user_email' => $data['user_email'] ?? 'N/A',
            'user_phone' => $data['user_phone'] ?? 'N/A',

            // Network Information
            'ip_address' => $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'country' => $data['country'] ?? 'Unknown',
            'country_code' => $data['country_code'] ?? 'XX',
            'isp' => $data['isp'] ?? 'Unknown ISP',
            'domain' => $data['domain'] ?? 'N/A',

            // Device Information
            'device_type' => $data['device_type'] ?? 'Unknown',
            'user_agent' => $data['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'browser' => $data['browser'] ?? 'Unknown',
            'operating_system' => $data['operating_system'] ?? 'Unknown',

            // Statistics
            'total_events' => (string)($data['total_events'] ?? 0),
            'failed_attempts' => (string)($data['failed_attempts'] ?? 0),
            'blacklist_hits' => (string)($data['blacklist_hits'] ?? 0),
            'risk_score' => (string)($data['risk_score'] ?? 0),

            // Logs
            'logs' => self::formatLogs($data['logs'] ?? []),

            // Additional Details
            'resource_accessed' => $data['resource_accessed'] ?? 'N/A',
            'action_performed' => $data['action_performed'] ?? 'N/A',
            'rule_triggered' => $data['rule_triggered'] ?? 'N/A',
            'correlation_id' => $data['correlation_id'] ?? self::generateCorrelationId(),
            'session_id' => $data['session_id'] ?? session_id(),

            // Dashboard Link
            'dashboard_url' => $data['dashboard_url'] ?? self::getDashboardUrl(),

            // Notes
            'additional_notes' => $data['additional_notes'] ?? 'No additional notes provided.',

            // Current Year
            'current_year' => date('Y'),

            // EmailJS Config
            'service_id' => self::SERVICE_ID,
            'template_id' => self::TEMPLATE_ID,
            'public_key' => self::PUBLIC_KEY,
        ];
    }

    /**
     * Format logs for EmailJS (up to 5 entries)
     *
     * @param array $logs Array of log entries
     * @return array Formatted logs
     */
    private static function formatLogs(array $logs): array
    {
        $formattedLogs = [];
        
        // Ensure we have exactly 5 log entries (pad with empty if needed)
        for ($i = 0; $i < 5; $i++) {
            if (isset($logs[$i])) {
                $formattedLogs[] = [
                    'time' => $logs[$i]['time'] ?? date('Y-m-d H:i:s'),
                    'level' => $logs[$i]['level'] ?? 'info',
                    'message' => $logs[$i]['message'] ?? '',
                ];
            } else {
                $formattedLogs[] = [
                    'time' => '',
                    'level' => 'info',
                    'message' => '',
                ];
            }
        }
        
        return $formattedLogs;
    }

    /**
     * Generate unique correlation ID
     *
     * @return string
     */
    public static function generateCorrelationId(): string
    {
        return 'WOLF-' . time() . '-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 9));
    }

    /**
     * Get dashboard URL
     *
     * @return string
     */
    private static function getDashboardUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Create alert data for suspicious login
     *
     * @param array $userData User data
     * @param array $networkData Network data
     * @param array $logs Activity logs
     * @return array
     */
    public static function createSuspiciousLoginAlert(array $userData, array $networkData, array $logs = []): array
    {
        return self::prepareSecurityAlert([
            'alert_type' => 'warning',
            'alert_message' => '⚠️ Suspicious Login Attempt Detected',
            'event_type' => 'Suspicious Login',
            'risk_level' => 'high',
            'detection_method' => 'Auto Scanner',

            'user_id' => $userData['id'] ?? 'N/A',
            'username' => $userData['username'] ?? 'Unknown',
            'user_email' => $userData['email'] ?? 'N/A',
            'user_phone' => $userData['phone'] ?? 'N/A',

            'ip_address' => $networkData['ip'] ?? '0.0.0.0',
            'country' => $networkData['country'] ?? 'Unknown',
            'country_code' => $networkData['country_code'] ?? 'XX',
            'isp' => $networkData['isp'] ?? 'Unknown',
            'domain' => $networkData['domain'] ?? 'N/A',

            'device_type' => $networkData['device_type'] ?? 'Unknown',
            'user_agent' => $networkData['user_agent'] ?? 'N/A',
            'browser' => $networkData['browser'] ?? 'Unknown',
            'operating_system' => $networkData['os'] ?? 'Unknown',

            'total_events' => (string)($userData['total_events'] ?? 1),
            'failed_attempts' => (string)($userData['failed_attempts'] ?? 1),
            'blacklist_hits' => (string)($userData['blacklist_hits'] ?? 0),
            'risk_score' => (string)($userData['risk_score'] ?? 75),

            'logs' => $logs,

            'resource_accessed' => '/login',
            'action_performed' => 'Login Attempt',
            'rule_triggered' => $userData['rule_triggered'] ?? 'Multiple Failed Login Attempts',

            'additional_notes' => $userData['notes'] ?? 'User attempted to login from a suspicious location or device.',
        ]);
    }

    /**
     * Create alert data for blacklist detection
     *
     * @param array $userData User data
     * @param array $networkData Network data
     * @param array $logs Activity logs
     * @return array
     */
    public static function createBlacklistAlert(array $userData, array $networkData, array $logs = []): array
    {
        return self::prepareSecurityAlert([
            'alert_type' => 'critical',
            'alert_message' => '🚨 CRITICAL: Blacklisted Entity Detected',
            'event_type' => 'Blacklist Detection',
            'risk_level' => 'high',
            'detection_method' => 'Blacklist Scanner',

            'user_id' => $userData['id'] ?? 'N/A',
            'username' => $userData['username'] ?? 'Unknown',
            'user_email' => $userData['email'] ?? 'N/A',
            'user_phone' => $userData['phone'] ?? 'N/A',

            'ip_address' => $networkData['ip'] ?? '0.0.0.0',
            'country' => $networkData['country'] ?? 'Unknown',
            'country_code' => $networkData['country_code'] ?? 'XX',
            'isp' => $networkData['isp'] ?? 'Unknown',
            'domain' => $networkData['domain'] ?? 'N/A',

            'device_type' => $networkData['device_type'] ?? 'Unknown',
            'user_agent' => $networkData['user_agent'] ?? 'N/A',
            'browser' => $networkData['browser'] ?? 'Unknown',
            'operating_system' => $networkData['os'] ?? 'Unknown',

            'total_events' => '1',
            'failed_attempts' => '0',
            'blacklist_hits' => '1',
            'risk_score' => '100',

            'logs' => $logs,

            'resource_accessed' => $userData['resource_accessed'] ?? 'N/A',
            'action_performed' => 'Access Attempt',
            'rule_triggered' => 'Blacklist Match',

            'dashboard_url' => self::getDashboardUrl() . '/blacklist',

            'additional_notes' => $userData['notes'] ?? 'This entity is on the blacklist and attempted to access the system.',
        ]);
    }

    /**
     * Create alert data for integrity scanner
     *
     * @param array $fileData File integrity data
     * @param array $logs Activity logs
     * @return array
     */
    public static function createIntegrityAlert(array $fileData, array $logs = []): array
    {
        return self::prepareSecurityAlert([
            'alert_type' => 'critical',
            'alert_message' => '🔴 File Integrity Violation Detected',
            'event_type' => 'Integrity Violation',
            'risk_level' => 'high',
            'detection_method' => 'Integrity Scanner',

            'user_id' => 'SYSTEM',
            'username' => 'System',
            'user_email' => 'N/A',
            'user_phone' => 'N/A',

            'ip_address' => 'SERVER-LOCAL',
            'country' => 'Server',
            'country_code' => 'SRV',
            'isp' => 'Internal',
            'domain' => 'localhost',

            'device_type' => 'Server',
            'user_agent' => 'N/A',
            'browser' => 'N/A',
            'operating_system' => 'Server',

            'total_events' => '1',
            'failed_attempts' => '0',
            'blacklist_hits' => '0',
            'risk_score' => (string)($fileData['risk_score'] ?? 90),

            'logs' => $logs,

            'resource_accessed' => $fileData['file_path'] ?? 'N/A',
            'action_performed' => 'File Modified',
            'rule_triggered' => 'Integrity Check Failed',
            'correlation_id' => self::generateCorrelationId(),
            'session_id' => 'N/A',

            'dashboard_url' => self::getDashboardUrl() . '/integrity-auto-scanner',

            'additional_notes' => $fileData['notes'] ?? 'A file has been modified and does not match the expected hash.',
        ]);
    }

    /**
     * Create alert data for risk correlation
     *
     * @param array $userData User data
     * @param array $networkData Network data
     * @param array $stats Statistics
     * @param array $logs Activity logs
     * @return array
     */
    public static function createRiskCorrelationAlert(
        array $userData,
        array $networkData,
        array $stats,
        array $logs = []
    ): array {
        return self::prepareSecurityAlert([
            'alert_type' => 'warning',
            'alert_message' => '⚠️ High Risk Correlation Detected',
            'event_type' => 'Risk Correlation',
            'risk_level' => $stats['risk_level'] ?? 'medium',
            'detection_method' => 'Risk Correlation Engine',

            'user_id' => $userData['id'] ?? 'N/A',
            'username' => $userData['username'] ?? 'Unknown',
            'user_email' => $userData['email'] ?? 'N/A',
            'user_phone' => $userData['phone'] ?? 'N/A',

            'ip_address' => $networkData['ip'] ?? '0.0.0.0',
            'country' => $networkData['country'] ?? 'Unknown',
            'country_code' => $networkData['country_code'] ?? 'XX',
            'isp' => $networkData['isp'] ?? 'Unknown',
            'domain' => $networkData['domain'] ?? 'N/A',

            'device_type' => $networkData['device_type'] ?? 'Unknown',
            'user_agent' => $networkData['user_agent'] ?? 'N/A',
            'browser' => $networkData['browser'] ?? 'Unknown',
            'operating_system' => $networkData['os'] ?? 'Unknown',

            'total_events' => (string)($stats['total_events'] ?? 0),
            'failed_attempts' => (string)($stats['failed_attempts'] ?? 0),
            'blacklist_hits' => (string)($stats['blacklist_hits'] ?? 0),
            'risk_score' => (string)($stats['risk_score'] ?? 0),

            'logs' => $logs,

            'resource_accessed' => $stats['resource_accessed'] ?? 'Multiple Resources',
            'action_performed' => $stats['action_performed'] ?? 'Multiple Actions',
            'rule_triggered' => $stats['rule_triggered'] ?? 'Risk Correlation Threshold Exceeded',
            'session_id' => $userData['session_id'] ?? session_id(),

            'dashboard_url' => self::getDashboardUrl() . '/risk-correlation',

            'additional_notes' => $userData['notes'] ?? 'Multiple risk factors have been correlated for this user.',
        ]);
    }

    /**
     * Create alert data for auto scanner detection
     *
     * @param array $scanData Scanner data
     * @param array $logs Activity logs
     * @return array
     */
    public static function createAutoScannerAlert(array $scanData, array $logs = []): array
    {
        return self::prepareSecurityAlert([
            'alert_type' => $scanData['alert_type'] ?? 'warning',
            'alert_message' => $scanData['alert_message'] ?? '🔍 Vulnerability Detected',
            'event_type' => 'Vulnerability Scan',
            'risk_level' => $scanData['risk_level'] ?? 'medium',
            'detection_method' => 'Auto Scanner',

            'user_id' => $scanData['user_id'] ?? 'SYSTEM',
            'username' => $scanData['username'] ?? 'System',
            'user_email' => $scanData['user_email'] ?? 'N/A',
            'user_phone' => $scanData['user_phone'] ?? 'N/A',

            'ip_address' => $scanData['ip_address'] ?? 'N/A',
            'country' => $scanData['country'] ?? 'N/A',
            'country_code' => $scanData['country_code'] ?? 'XX',
            'isp' => $scanData['isp'] ?? 'N/A',
            'domain' => $scanData['domain'] ?? 'N/A',

            'device_type' => 'Scanner',
            'user_agent' => 'Wolf Auto Scanner',
            'browser' => 'N/A',
            'operating_system' => 'Server',

            'total_events' => (string)($scanData['total_vulnerabilities'] ?? 1),
            'failed_attempts' => '0',
            'blacklist_hits' => '0',
            'risk_score' => (string)($scanData['risk_score'] ?? 50),

            'logs' => $logs,

            'resource_accessed' => $scanData['resource_accessed'] ?? 'N/A',
            'action_performed' => 'Vulnerability Scan',
            'rule_triggered' => $scanData['vulnerability_type'] ?? 'Security Vulnerability',

            'dashboard_url' => self::getDashboardUrl() . '/vuln',

            'additional_notes' => $scanData['notes'] ?? 'A security vulnerability has been detected during automated scanning.',
        ]);
    }

    /**
     * Generate correlation ID
     *
     * @return string
     */
    public static function generateCorrelationId(): string
    {
        return 'WOLF-' . time() . '-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 9));
    }

    /**
     * Get dashboard URL
     *
     * @return string
     */
    private static function getDashboardUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * Get EmailJS configuration
     *
     * @return array
     */
    public static function getConfig(): array
    {
        return [
            'service_id' => self::SERVICE_ID,
            'template_id' => self::TEMPLATE_ID,
            'public_key' => self::PUBLIC_KEY,
        ];
    }

    /**
     * Format alert data as JSON for JavaScript
     *
     * @param array $alertData Alert data
     * @return string JSON string
     */
    public static function toJson(array $alertData): string
    {
        return json_encode($alertData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Example: Trigger email alert from PHP
     * This sets data that JavaScript will pick up and send
     *
     * @param \Base $f3 Fat-Free Framework instance
     * @param array $alertData Alert data
     * @return void
     */
    public static function triggerEmailAlert(\Base $f3, array $alertData): void
    {
        $preparedData = self::prepareSecurityAlert($alertData);
        $f3->set('SECURITY_ALERT_DATA', self::toJson($preparedData));
        $f3->set('TRIGGER_EMAIL_ALERT', true);
    }

    /**
     * Check if today is an allowed day for sending
     *
     * @param array|string $allowedDays Array of day names or 'all'
     * @return bool
     */
    public static function isAllowedDay($allowedDays = 'all'): bool
    {
        if ($allowedDays === 'all' || $allowedDays === '*') {
            return true;
        }

        $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDay = strtolower(date('l'));

        if (is_array($allowedDays)) {
            return in_array($currentDay, array_map('strtolower', $allowedDays), true);
        }

        return strtolower($allowedDays) === $currentDay;
    }

    /**
     * Trigger email alert only on specific days
     *
     * @param \Base $f3 Fat-Free Framework instance
     * @param array $alertData Alert data
     * @param array|string $allowedDays Days to send ('all', ['monday', 'friday'], etc.)
     * @param bool $forceSend Force send regardless of day
     * @return bool True if triggered, false if skipped
     */
    public static function triggerEmailAlertOnDay(
        \Base $f3,
        array $alertData,
        $allowedDays = 'all',
        bool $forceSend = false
    ): bool {
        // Check if sending is allowed today
        if (!$forceSend && !self::isAllowedDay($allowedDays)) {
            $today = date('l');
            error_log("Wolf EmailJS: Email not sent - today is {$today}, allowed days: " . json_encode($allowedDays));
            return false;
        }

        // Trigger the alert
        self::triggerEmailAlert($f3, $alertData);
        return true;
    }

    /**
     * Trigger alert only on weekdays (Monday-Friday)
     *
     * @param \Base $f3 Fat-Free Framework instance
     * @param array $alertData Alert data
     * @return bool
     */
    public static function triggerEmailAlertOnWeekdays(\Base $f3, array $alertData): bool
    {
        return self::triggerEmailAlertOnDay(
            $f3,
            $alertData,
            ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']
        );
    }

    /**
     * Trigger alert only on weekends (Saturday-Sunday)
     *
     * @param \Base $f3 Fat-Free Framework instance
     * @param array $alertData Alert data
     * @return bool
     */
    public static function triggerEmailAlertOnWeekends(\Base $f3, array $alertData): bool
    {
        return self::triggerEmailAlertOnDay(
            $f3,
            $alertData,
            ['saturday', 'sunday']
        );
    }

    /**
     * Trigger alert on specific day of week
     *
     * @param \Base $f3 Fat-Free Framework instance
     * @param array $alertData Alert data
     * @param string $dayName Day name (e.g., 'monday', 'friday')
     * @return bool
     */
    public static function triggerEmailAlertOnSpecificDay(\Base $f3, array $alertData, string $dayName): bool
    {
        return self::triggerEmailAlertOnDay($f3, $alertData, [$dayName]);
    }
}
