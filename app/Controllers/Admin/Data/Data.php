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

namespace Wolf\Controllers\Admin\Data;

class Data extends \Wolf\Controllers\Admin\Base\Data {
    // POST requests
    public function enrichEntity(): array {
        $controller = new \Wolf\Controllers\Admin\Enrichment\Navigation();

        return $controller->enrichEntity();
    }

    public function saveRule(): array {
        $controller = new \Wolf\Controllers\Admin\Rules\Navigation();

        return $controller->saveRule();
    }

    public function removeFromBlacklist(): array {
        $controller = new \Wolf\Controllers\Admin\Blacklist\Navigation();

        return $controller->removeItemFromList();
    }

    public function removeFromWatchlist(): array {
        $controller = new \Wolf\Controllers\Admin\Watchlist\Navigation();

        return $controller->removeUserFromList();
    }

    public function manageUser(): array {
        $controller = new \Wolf\Controllers\Admin\User\Navigation();

        return $controller->manageUser();
    }

    // GET requests
    public function checkRule(): array {
        $controller = new \Wolf\Controllers\Admin\Rules\Navigation();

        return $controller->checkRule();
    }

    public function getTimeFrameTotal(): array {
        $controller = new \Wolf\Controllers\Admin\Totals\Navigation();

        return $controller->getTimeFrameTotal();
    }

    public function getCountries(): array {
        $controller = new \Wolf\Controllers\Admin\Countries\Navigation();

        return $controller->getList();
    }

    public function getMap(): array {
        $controller = new \Wolf\Controllers\Admin\Countries\Navigation();

        return $controller->getMap();
    }

    public function getIps(): array {
        $controller = new \Wolf\Controllers\Admin\IPs\Navigation();

        return $controller->getList();
    }

    public function getEvents(): array {
        $controller = new \Wolf\Controllers\Admin\Events\Navigation();

        return $controller->getList();
    }

    public function getLogbook(): array {
        $controller = new \Wolf\Controllers\Admin\Logbook\Navigation();

        return $controller->getList();
    }

    public function getUsers(): array {
        $controller = new \Wolf\Controllers\Admin\Users\Navigation();

        return $controller->getList();
    }

    public function getUserAgents(): array {
        $controller = new \Wolf\Controllers\Admin\UserAgents\Navigation();

        return $controller->getList();
    }

    public function getDevices(): array {
        $controller = new \Wolf\Controllers\Admin\Devices\Navigation();

        return $controller->getList();
    }

    public function getResources(): array {
        $controller = new \Wolf\Controllers\Admin\Resources\Navigation();

        return $controller->getList();
    }

    public function getDashboardStat(): array {
        $controller = new \Wolf\Controllers\Admin\Home\Navigation();

        return $controller->getDashboardStat();
    }

    public function getTopTen(): array {
        $controller = new \Wolf\Controllers\Admin\Home\Navigation();

        return $controller->getTopTen();
    }

    public function getChart(): array {
        $controller = new \Wolf\Controllers\Admin\Home\Navigation();

        return $controller->getChart();
    }

    public function getEventDetails(): array {
        $controller = new \Wolf\Controllers\Admin\Events\Navigation();

        return $controller->getEventDetails();
    }

    public function getFieldEventDetails(): array {
        $controller = new \Wolf\Controllers\Admin\FieldAuditTrail\Navigation();

        return $controller->getFieldEventDetails();
    }

    public function getLogbookDetails(): array {
        $controller = new \Wolf\Controllers\Admin\Logbook\Navigation();

        return $controller->getLogbookDetails();
    }

    public function getEmailDetails(): array {
        $controller = new \Wolf\Controllers\Admin\Emails\Navigation();

        return $controller->getEmailDetails();
    }

    public function getPhoneDetails(): array {
        $controller = new \Wolf\Controllers\Admin\Phones\Navigation();

        return $controller->getPhoneDetails();
    }

    public function getUserDetails(): array {
        $controller = new \Wolf\Controllers\Admin\UserDetails\Navigation();

        return $controller->getUserDetails();
    }

    /*public function getUserEnrichmentDetails(): array {
        $controller = new \Wolf\Controllers\Admin\UserDetails\Navigation();

        return $controller->getUserEnrichmentDetails();
    }*/

    public function getNotCheckedEntitiesCount(): array {
        $controller = new \Wolf\Controllers\Admin\Enrichment\Navigation();

        return $controller->getNotCheckedEntitiesCount();
    }

    public function getEmails(): array {
        $controller = new \Wolf\Controllers\Admin\Emails\Navigation();

        return $controller->getList();
    }

    public function getPhones(): array {
        $controller = new \Wolf\Controllers\Admin\Phones\Navigation();

        return $controller->getList();
    }

    public function getFieldAuditTrail(): array {
        $controller = new \Wolf\Controllers\Admin\FieldAuditTrail\Navigation();

        return $controller->getList();
    }

    public function getFieldAudits(): array {
        $controller = new \Wolf\Controllers\Admin\FieldAudits\Navigation();

        return $controller->getList();
    }

    public function getUserScoreDetails(): array {
        $controller = new \Wolf\Controllers\Admin\User\Navigation();

        return $controller->getUserScoreDetails();
    }

    public function getIsps(): array {
        $controller = new \Wolf\Controllers\Admin\ISPs\Navigation();

        return $controller->getList();
    }

    public function getDomains(): array {
        $controller = new \Wolf\Controllers\Admin\Domains\Navigation();

        return $controller->getList();
    }

    public function getReviewUsersQueue(): array {
        $controller = new \Wolf\Controllers\Admin\ReviewQueue\Navigation();

        return $controller->getList();
    }

    public function getReviewUsersQueueCount(): array {
        $controller = new \Wolf\Controllers\Admin\ReviewQueue\Navigation();

        return $controller->setNotReviewedCount(false);     // no cache
    }

    public function getBlacklistUsersCount(): array {
        $controller = new \Wolf\Controllers\Admin\Blacklist\Navigation();

        return $controller->setBlacklistUsersCount(false);  // no cache
    }

    public function getIspDetails(): array {
        $controller = new \Wolf\Controllers\Admin\ISP\Navigation();

        return $controller->getIspDetails();
    }

    public function getIpDetails(): array {
        $controller = new \Wolf\Controllers\Admin\IP\Navigation();

        return $controller->getIpDetails();
    }

    public function getDeviceDetails(): array {
        $controller = new \Wolf\Controllers\Admin\Devices\Navigation();

        return $controller->getDeviceDetails();
    }

    public function getUserAgentDetails(): array {
        $controller = new \Wolf\Controllers\Admin\UserAgent\Navigation();

        return $controller->getUserAgentDetails();
    }

    public function getDomainDetails(): array {
        $controller = new \Wolf\Controllers\Admin\Domain\Navigation();

        return $controller->getDomainDetails();
    }

    public function getSearchResults(): array {
        $controller = new \Wolf\Controllers\Admin\Search\Navigation();

        return $controller->getSearchResults();
    }

    public function getBlacklist(): array {
        $controller = new \Wolf\Controllers\Admin\Blacklist\Navigation();

        return $controller->getList();
    }

    public function getUsageStats(): array {
        $controller = new \Wolf\Controllers\Admin\Api\Navigation();

        return $controller->getUsageStats();
    }

    public function getCurrentTime(): array {
        $controller = new \Wolf\Controllers\Admin\Home\Navigation();

        return $controller->getCurrentTime();
    }

    public function getConstants(): array {
        $controller = new \Wolf\Controllers\Admin\Home\Navigation();

        return $controller->getConstants();
    }

    /**
     * Cyber Bot chat – sends message to Ollama tinyllama:latest with cybersecurity context.
     */
    public function cyberChat(): array {
        try {
            $message = trim((string)($this->f3->get('REQUEST.message') ?? ''));
            if ($message === '') {
                return ['success' => false, 'error' => 'Empty message.'];
            }

            $response = \Wolf\Utils\Ollama::chat($message);
            if ($response === null || $response === '') {
                return [
                    'success' => false,
                    'error' => 'Ollama connection failed. Ensure Ollama is running with tinyllama:latest (ollama run tinyllama).',
                ];
            }

            return ['success' => true, 'response' => $response];
        } catch (\Throwable $e) {
            \Wolf\Utils\Logger::log('CyberChat', $e->getMessage() . "\n" . $e->getTraceAsString());
            return ['success' => false, 'error' => 'Server error. Please try again.'];
        }
    }

    /**
     * Check Ollama connection and tinyllama model availability.
     */
    public function getOllamaStatus(): array {
        $connected = \Wolf\Utils\Ollama::isConnected();

        return ['connected' => $connected];
    }

    /**
     * Scan URL via CheckPhish API for phishing detection.
     */
    public function checkPhish(): array {
        $phishingData = new \Wolf\Controllers\Admin\PhishingDetector\Data();

        return $phishingData->scanUrl();
    }

    /**
     * API Tester – proxy HTTP request to target URL and return response details.
     */
    public function apiTesterRequest(): array {
        $apiTesterData = new \Wolf\Controllers\Admin\ApiTester\Data();

        return $apiTesterData->proxyRequest();
    }

    /**
     * Get current Report Sender total sent count for the current operator.
     */
    public function getReportSenderCount(): array {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $count  = \Wolf\Utils\ReportSenderCount::getCount($apiKey);

        return ['total' => $count];
    }

    /**
     * Increment Report Sender sent count and return the new total.
     */
    public function reportSenderIncrement(): array {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $total  = \Wolf\Utils\ReportSenderCount::increment($apiKey);

        return ['total' => $total];
    }

}
