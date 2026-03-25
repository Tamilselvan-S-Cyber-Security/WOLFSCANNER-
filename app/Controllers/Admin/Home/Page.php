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

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminHome';

    public function getPageParams(): array {
        $pageParams = [
            'LOAD_DATATABLE'    => true,
            'LOAD_AUTOCOMPLETE' => true,
            'HTML_FILE'         => 'admin/home.html',
            'JS'                => 'admin_dashboard.js',
        ];

        return parent::applyPageParams($pageParams);
    }
}
