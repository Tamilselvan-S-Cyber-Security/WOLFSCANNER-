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

namespace Wolf\Controllers\Admin\Home;

class Navigation extends \Wolf\Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = new Page();
    }

    public function showIndexPage(): void {
        \Wolf\Utils\Routes::redirectIfUnlogged('/login');

        parent::showIndexPage();
    }

    public function getDashboardStat(): array {
        $mode = \Wolf\Utils\Conversion::getStringRequestParam('mode');
        $dateRange = \Wolf\Utils\DateRange::getDatesRangeFromRequest();

        return $this->apiKey ? $this->controller->getStat($mode, $dateRange, $this->apiKey) : [];
    }

    public function getTopTen(): array {
        $mode = \Wolf\Utils\Conversion::getStringRequestParam('mode');
        $dateRange = \Wolf\Utils\DateRange::getDatesRangeFromRequest();

        return $this->apiKey ? $this->controller->getTopTen($mode, $dateRange, $this->apiKey) : [];
    }

    public function getChart(): array {
        $mode = \Wolf\Utils\Conversion::getStringRequestParam('mode');

        return $this->apiKey ? $this->controller->getChart($mode, $this->apiKey) : [];
    }

    public function getCurrentTime(): array {
        return $this->operator ? $this->controller->getCurrentTime($this->operator) : [];
    }

    public function getConstants(): array {
        return $this->controller->getConstants();
    }
}
