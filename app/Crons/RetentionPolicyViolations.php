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

class RetentionPolicyViolations extends Base {
    public function process(): void {
        $this->addLog('Start retention policy violations.');

        $eventsModel = new \Wolf\Models\Events();
        $retentionModel = new \Wolf\Models\RetentionPolicies();
        $fieldAuditModel = new \Wolf\Models\FieldAuditTrail();

        $retentionKeys = $retentionModel->getRetentionKeys();
        $cnt = 0;
        $fieldCnt = 0;

        foreach ($retentionKeys as $key) {
            // insuring clause
            if ($key['retention_policy'] > 0) {
                $cnt += $eventsModel->retentionDeletion($key['retention_policy'], $key['id']);
                $fieldCnt += $fieldAuditModel->retentionDeletion($key['retention_policy'], $key['id']);
            }
        }

        $this->addLog(sprintf('Deleted %s events and %s field audit trails for %s operators due to retention policy violations.', $cnt, $fieldCnt, count($retentionKeys)));
    }
}
