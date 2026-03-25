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

class RiskScoreQueueHandler extends BaseQueue {
    private \Wolf\Controllers\Admin\Rules\Data $rulesController;

    public function __construct() {
        $this->rulesController = new \Wolf\Controllers\Admin\Rules\Data();
        $this->rulesController->buildEvaluationModels();
    }

    public function process(): void {
        $batchSize = \Wolf\Utils\Variables::getAccountOperationQueueBatchSize();
        $queueModel = new \Wolf\Models\Queue();
        $keys = $queueModel->getNextBatchKeys(\Wolf\Utils\Constants::get()->RISK_SCORE_QUEUE_ACTION_TYPE, $batchSize);

        parent::baseProcess(\Wolf\Utils\Constants::get()->RISK_SCORE_QUEUE_ACTION_TYPE);

        $blacklist = new \Wolf\Controllers\Admin\Blacklist\Data();
        $reviewQueue = new \Wolf\Controllers\Admin\ReviewQueue\Data();

        foreach ($keys as $key) {
            $blacklist->setBlacklistUsersCount(false, $key);
            $reviewQueue->setNotReviewedCount(false, $key);
        }
    }

    protected function processItem(array $item): void {
        $this->rulesController->evaluateUser($item['event_account'], $item['key'], true);
    }
}
