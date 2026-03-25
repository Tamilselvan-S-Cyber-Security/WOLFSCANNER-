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

class ErrorHandler {
    public static function getErrorDetails(\Base $f3): array {
        $errorTraceArray = [];

        $errorTraceString = $f3->get('ERROR.trace');
        $errorTraceArray = preg_split('/$\R?^/m', $errorTraceString);
        $maximalStringIndex = 0;
        $maximalStringLength = 0;
        $iters = count($errorTraceArray);

        for ($i = 0; $i < $iters; ++$i) {
            $currentStringLength = strlen($errorTraceArray[$i]);
            if ($maximalStringLength < $currentStringLength) {
                $maximalStringIndex = $i;
                $maximalStringLength = $currentStringLength;
            }
        }

        if ($iters > 1) {
            array_splice($errorTraceArray, $maximalStringIndex, 1);
        }

        $iters = count($errorTraceArray);
        for ($i = 0; $i < $iters; ++$i) {
            $errorTraceArray[$i] = strip_tags($errorTraceArray[$i]);
            $errorTraceArray[$i] = str_replace(['&gt;', '&lt;'], ['>', '<'], $errorTraceArray[$i]);
        }

        $errorCode = $f3->get('ERROR.code');
        $errorMessage = join(', ', ['ERROR_' . $errorCode, $f3->get('ERROR.text')]);

        return [
            'ip' => $f3->get('IP'),
            'code' => $errorCode,
            'message' => $errorMessage,
            'trace' => join('<br>', $errorTraceArray),
            'date' => date('l jS \of F Y h:i:s A'),
            'post' => $f3->get('POST'),
            'get' => $f3->get('GET'),
        ];
    }

    public static function saveErrorInformation(\Base $f3, array $errorData): void {
        \Wolf\Utils\Logger::log(null, $errorData['message']);

        $errorTraceArray = explode('<br>', $errorData['trace']);
        $printErrorTraceToLog = $f3->get('PRINT_ERROR_TRACE_TO_LOG');
        if ($printErrorTraceToLog) {
            $iters = count($errorTraceArray);

            for ($i = 0; $i < $iters; ++$i) {
                \Wolf\Utils\Logger::log(null, $errorTraceArray[$i]);
            }
        }

        $database = \Wolf\Utils\Database::getDb();
        if ($database && \Wolf\Utils\Routes::getCurrentRequestOperator()) {
            $errorData['sql_log'] = $database->log();
            $logModel = new \Wolf\Models\Log();
            $logModel->insertRecord($errorData);

            \Wolf\Utils\Logger::log('SQL', $errorData['sql_log']);
        }

        if ($errorData['code'] === 500) {
            $toName = 'Admin';
            $toAddress = \Wolf\Utils\Variables::getAdminEmail();
            if ($toAddress === null) {
                \Wolf\Utils\Logger::log('Log mail error', 'ADMIN_EMAIL is not set');

                return;
            }

            $subject = $f3->get('error_email_subject') ?? '%s';
            $subject = sprintf($subject, $errorData['code']);

            $currentTime = date('d-m-Y H:i:s');
            $errorMessage = $errorData['message'];
            $errorTrace = $errorData['trace'];

            $hosts = json_encode(\Wolf\Utils\Variables::getHosts());

            $message = $f3->get('error_email_body_template');
            $message = sprintf($message, $currentTime, $hosts, $errorMessage, $errorTrace);

            \Wolf\Utils\Mailer::send($toName, $toAddress, $subject, $message, true);
        }
    }

    protected static function getAjaxErrorMessage(array $errorData): string|false {
        return json_encode(
            [
                'status' => false,
                'code' => $errorData['code'],
                'message' => sprintf('Request finished with code %s', $errorData['code']),
            ],
        );
    }

    public static function getOnErrorHandler(): callable {
        /**
         * Custom onError handler: http://stackoverflow.com/questions/19763414/fat-free-framework-f3-custom-404-page-and-others-errors, https://groups.google.com/forum/#!topic/f3-framework/BOIrLs5_aEA
         * We can can use $f3->get('ERROR.text'), and decide which template should be displayed.
         *
         * @param $f3
         */
        return function (\Base $f3): void {
            $isAjax = $f3->get('AJAX');

            $errorData = self::getErrorDetails($f3);

            // clean template if anything was rendered already
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            self::saveErrorInformation($f3, $errorData);

            if ($errorData['code'] === 403 && !$isAjax) {
                $f3->reroute('/logout');

                return;
            }

            // Add handling 404 error
            if ($errorData['code'] === 404) {
            }

            if ($isAjax) {
                echo self::getAjaxErrorMessage($errorData);

                return;
            }

            $response = new \Wolf\Views\Frontend();
            $pageController = new \Wolf\Controllers\Pages\Error();

            $errorData['message'] = 'ERROR_' . $errorData['code'];
            $errorData['raw'] = false;

            if ($errorData['code'] !== 404) {
                $errorData['extra_message'] = $f3->get('ErrorPage_extra_message');
                $errorData['raw'] = true;
            }

            if ($errorData['code'] === 400) {
                $errorData['message'] = 'Error code ' . \Wolf\Utils\ErrorCodes::INVALID_HOSTNAME;
                $errorData['extra_message'] = 'Visit page via correct hostname: ' . \Wolf\Utils\Variables::getHostWithProtocol() . $f3->get('PATH');
            }

            if ($errorData['code'] === 503) {
                $errorData['message'] = 'Error code ' . \Wolf\Utils\ErrorCodes::FAILED_DB_CONNECT;
                $errorData['extra_message'] = 'Database connection failed.';
            }

            if ($errorData['code'] === 422) {
                $errorData['message'] = 'Error code ' . \Wolf\Utils\ErrorCodes::INCOMPLETE_CONFIG;
                $errorData['extra_message'] = 'App configuration is incomplete. Check config/local/config.local.ini and possible environment overrides.';
            }

            unset($errorData['trace']);
            $pageParams = $pageController->getPageParams($errorData);

            $response->data = $pageParams;
            echo $response->render();
        };
    }

    public static function getCronErrorHandler(): callable {
        return function (\Base $f3): void {
            $errorData = self::getErrorDetails($f3);
            self::saveErrorInformation($f3, $errorData);
        };
    }

    public static function exceptionErrorHandler(int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}
