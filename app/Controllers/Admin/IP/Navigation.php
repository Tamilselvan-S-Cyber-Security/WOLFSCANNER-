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

namespace Wolf\Controllers\Admin\IP;

class Navigation extends \Wolf\Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = new Page();
    }

    public function getIpDetails(): array {
        $ipId = \Wolf\Utils\Conversion::getIntRequestParam('ipId');
        $hasAccess = $this->controller->checkIfOperatorHasAccess($ipId, $this->apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        return $this->controller->getIpDetails($ipId, $this->apiKey);
    }
}
