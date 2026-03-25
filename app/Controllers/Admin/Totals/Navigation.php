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

namespace Wolf\Controllers\Admin\Totals;

class Navigation extends \Wolf\Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = null;
    }

    public function getTimeFrameTotal(): array {
        $ids        = \Wolf\Utils\Conversion::getArrayRequestParam('ids');
        $type       = \Wolf\Utils\Conversion::getStringRequestParam('type');
        $startDate  = \Wolf\Utils\Conversion::getStringRequestParam('startDate');
        $endDate    = \Wolf\Utils\Conversion::getStringRequestParam('endDate');

        return $this->controller->getTimeFrameTotal($ids, $type, $startDate, $endDate, $this->apiKey);
    }
}
