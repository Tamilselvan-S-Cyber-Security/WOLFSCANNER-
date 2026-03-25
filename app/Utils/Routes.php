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

class Routes {
    private static function getF3(): \Base {
        return \Base::instance();
    }

    public static function getCurrentRequestOperator(): ?\Wolf\Entities\Operator {
        return self::getF3()->get('CURRENT_USER');
    }

    public static function setCurrentRequestOperator(): void {
        self::getF3()->set('CURRENT_USER', self::getCurrentSessionOperator());
    }

    public static function getCurrentSessionOperator(): ?\Wolf\Entities\Operator {
        $loggedInOperatorId = \Wolf\Utils\Conversion::intValCheckEmpty(self::getF3()->get('SESSION.active_user_id'));

        return $loggedInOperatorId ? \Wolf\Entities\Operator::getById($loggedInOperatorId) : null;
    }

    public static function getCurrentRequestApiKey(): ?\Wolf\Entities\ApiKey {
        return self::getF3()->get('CURRENT_KEY');
    }

    public static function setCurrentRequestApiKey(): void {
        self::getF3()->set('CURRENT_KEY', self::getCurrentSessionApiKey());
    }

    public static function getCurrentSessionApiKey(): ?\Wolf\Entities\ApiKey {
        $keyId = self::getF3()->get('TEST_API_KEY_ID');

        if (!$keyId) {
            $keyId = \Wolf\Utils\Conversion::intValCheckEmpty(self::getF3()->get('SESSION.active_key_id'));
        }

        return $keyId ? \Wolf\Entities\ApiKey::getById($keyId) : null;
    }

    public static function redirectIfUnlogged(string $targetPage = '/'): void {
        if (!boolval(self::getCurrentRequestOperator())) {
            self::getF3()->reroute($targetPage);
        }
    }

    public static function redirectIfLogged(): void {
        if (boolval(self::getCurrentRequestOperator())) {
            self::getF3()->reroute('/');
        }
    }

    public static function callExtra(string $method, mixed ...$extra): string|array|null {
        $method = \Base::instance()->get('EXTRA_' . $method);

        return $method && is_callable($method) ? $method(...$extra) : null;
    }
}
