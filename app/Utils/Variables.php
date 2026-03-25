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

class Variables {
    private static function getF3(): \Base {
        return \Base::instance();
    }

    public static function getDB(): ?string {
        return getenv('DATABASE_URL') ?: self::getF3()->get('DATABASE_URL');
    }

    public static function getConfigFile(): string {
        return getenv('CONFIG_FILE') ?: 'local/config.local.ini';
    }

    public static function getHosts(): array {
        $env = getenv('SITE');
        $conf = self::getF3()->get('SITE');

        return $env ? explode(',', $env) : (is_array($conf) ? $conf : [$conf]);
    }

    public static function getHost(): string {
        return self::getHosts()[0];
    }

    public static function getAdminEmail(): ?string {
        return getenv('ADMIN_EMAIL') ?: self::getF3()->get('ADMIN_EMAIL');
    }

    public static function getMailLogin(): ?string {
        return getenv('MAIL_LOGIN') ?: self::getF3()->get('MAIL_LOGIN');
    }

    public static function getMailPassword(): ?string {
        return getenv('MAIL_PASS') ?: self::getF3()->get('MAIL_PASS');
    }

    /** Inbox for security notifications when an operator signs in at /login (PHPMailer). */
    public static function getLoginAlertEmail(): ?string {
        $addr = getenv('LOGIN_ALERT_EMAIL') ?: self::getF3()->get('LOGIN_ALERT_EMAIL');
        $addr = is_string($addr) ? trim($addr) : '';

        return $addr !== '' ? $addr : null;
    }

    /** EmailJS (browser): Public Key from https://dashboard.emailjs.com/admin/account */
    public static function getEmailJsPublicKey(): string {
        $v = getenv('EMAILJS_PUBLIC_KEY') ?: self::getF3()->get('EMAILJS_PUBLIC_KEY');

        return trim(is_string($v) ? $v : '');
    }

    public static function getEmailJsServiceId(): string {
        $v = getenv('EMAILJS_SERVICE_ID') ?: self::getF3()->get('EMAILJS_SERVICE_ID');

        return trim(is_string($v) ? $v : '');
    }

    public static function getEmailJsTemplateId(): string {
        $v = getenv('EMAILJS_TEMPLATE_ID') ?: self::getF3()->get('EMAILJS_TEMPLATE_ID');

        return trim(is_string($v) ? $v : '');
    }

    public static function getLoginAlertOnFailure(): bool {
        $variable = getenv('LOGIN_ALERT_ON_FAILURE') ?: self::getF3()->get('LOGIN_ALERT_ON_FAILURE') ?? 'false';

        return \Wolf\Utils\Conversion::filterBool($variable) ?? false;
    }

    public static function getEnrichmentApi(): string {
        return getenv('ENRICHMENT_API') ?: self::getF3()->get('ENRICHMENT_API');
    }

    public static function getOllamaUrl(): string {
        return getenv('OLLAMA_URL') ?: self::getF3()->get('OLLAMA_URL') ?: 'http://localhost:11434';
    }

    public static function getCheckPhishApiKey(): ?string {
        $key = getenv('CHECKPHISH_API_KEY') ?: self::getF3()->get('CHECKPHISH_API_KEY');

        return is_string($key) && $key !== '' ? $key : null;
    }

    /** SHA-256 cloud reputation lookup (optional; key from operator config or env). */
    public static function getCloudFileReputationApiKey(): ?string {
        $key = getenv('CLOUD_FILE_REPUTATION_API_KEY') ?: getenv('VIRUSTOTAL_API_KEY');
        if (!is_string($key) || $key === '') {
            $f3 = self::getF3();
            $key = $f3->get('CLOUD_FILE_REPUTATION_API_KEY') ?: $f3->get('VIRUSTOTAL_API_KEY');
        }
        $key = is_string($key) ? trim($key) : '';

        return $key !== '' ? $key : null;
    }

    public static function getPepper(): string {
        return getenv('PEPPER') ?: self::getF3()->get('PEPPER');
    }

    public static function getLogbookLimit(): int {
        $value = getenv('LOGBOOK_LIMIT') ?: self::getF3()->get('LOGBOOK_LIMIT') ?: \Wolf\Utils\Constants::get()->LOGBOOK_LIMIT;

        return \Wolf\Utils\Conversion::intValCheckEmpty($value, \Wolf\Utils\Constants::get()->LOGBOOK_LIMIT);
    }

    public static function getForgotPasswordAllowed(): bool {
        $variable = getenv('ALLOW_FORGOT_PASSWORD') ?: self::getF3()->get('ALLOW_FORGOT_PASSWORD') ?? 'false';

        return \Wolf\Utils\Conversion::filterBool($variable) ?? false;
    }

    public static function getEmailPhoneAllowed(): bool {
        $variable = getenv('ALLOW_EMAIL_PHONE') ?: self::getF3()->get('ALLOW_EMAIL_PHONE') ?? 'false';

        return \Wolf\Utils\Conversion::filterBool($variable) ?? false;
    }

    public static function getForceHttps(): bool {
        // set 'false' string if FORCE_HTTPS wasn't set due to filter_var() issues
        $variable = getenv('FORCE_HTTPS') ?: self::getF3()->get('FORCE_HTTPS') ?? 'false';

        return \Wolf\Utils\Conversion::filterBool($variable) ?? true;
    }

    public static function getHostWithProtocol(): string {
        $host = self::getHost();

        if (!str_starts_with($host, '[') && \Wolf\Utils\Conversion::filterIpGetType($host) === 6) {
            $host = '[' . $host . ']';
        }

        return (self::getForceHttps() ? 'https://' : 'http://') . $host;
    }

    public static function getHostWithProtocolAndBase(): string {
        return self::getHostWithProtocol() . self::getF3()->get('BASE');
    }

    public static function getAccountOperationQueueBatchSize(): int {
        return \Wolf\Utils\Conversion::intValCheckEmpty(getenv('ACCOUNT_OPERATION_QUEUE_BATCH_SIZE'), \Wolf\Utils\Constants::get()->ACCOUNT_OPERATION_QUEUE_BATCH_SIZE);
    }

    public static function getNewEventsBatchSize(): int {
        return \Wolf\Utils\Conversion::intValCheckEmpty(getenv('NEW_EVENTS_BATCH_SIZE'), \Wolf\Utils\Constants::get()->NEW_EVENTS_BATCH_SIZE);
    }

    public static function getRuleUsersBatchSize(): int {
        return \Wolf\Utils\Conversion::intValCheckEmpty(getenv('RULE_USERS_BATCH_SIZE'), \Wolf\Utils\Constants::get()->RULE_USERS_BATCH_SIZE);
    }

    public static function getAvailableTimezones(): array {
        return array_intersect_key(self::getF3()->get('timezones'), array_flip(\DateTimeZone::listIdentifiers()));
    }

    public static function completedConfig(): bool {
        return
            (getenv('SITE') || self::getF3()->get('SITE')) &&
            (getenv('PEPPER') || self::getF3()->get('PEPPER')) &&
            (getenv('ENRICHMENT_API') || self::getF3()->get('ENRICHMENT_API')) &&
            (getenv('DATABASE_URL') || self::getF3()->get('DATABASE_URL'));
    }
}
