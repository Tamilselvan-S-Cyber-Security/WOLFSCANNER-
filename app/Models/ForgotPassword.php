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

class ForgotPassword extends \Wolf\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_operators_forgot_password';

    public function insertRecord(int $operatorId): string {
        $params = [
            ':operator_id'  => $operatorId,
            ':status'       => 'unused',
            ':invalidated'  => 'invalidated',
        ];

        $query = (
            'UPDATE dshb_operators_forgot_password
            SET
                status = :invalidated
            WHERE
                dshb_operators_forgot_password.operator_id = :operator_id AND
                dshb_operators_forgot_password.status = :status'
        );

        $this->execQuery($query, $params);

        $renewKey = \Wolf\Utils\Access::pseudoRandString(32);

        $params = [
            ':operator_id'  => $operatorId,
            ':renew_key'    => $renewKey,
            ':status'       => 'unused',
        ];

        $query = (
            'INSERT INTO dshb_operators_forgot_password (
                renew_key, status, operator_id
            ) VALUES (
                :renew_key, :status, :operator_id
            )'
        );

        $this->execQuery($query, $params);

        return $renewKey;
    }

    public function getUnusedByRenewKey(string $renewKey): ?string {
        $params = [
            ':renew_key'    => $renewKey,
            ':status'       => 'unused',
        ];

        $query = (
            'SELECT
                created_at
            FROM
                dshb_operators_forgot_password
            WHERE
                dshb_operators_forgot_password.renew_key = :renew_key AND
                dshb_operators_forgot_password.status = :status'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['created_at'] ?? null;
    }

    public function useByRenewKey(string $renewKey): ?int {
        $params = [
            ':renew_key'    => $renewKey,
            ':status'       => 'unused',
            ':used'         => 'used',
        ];

        $query = (
            'UPDATE dshb_operators_forgot_password
            SET
                status = :used
            WHERE
                dshb_operators_forgot_password.renew_key = :renew_key AND
                dshb_operators_forgot_password.status = :status
            RETURNING operator_id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['operator_id'] ?? null;
    }
}
