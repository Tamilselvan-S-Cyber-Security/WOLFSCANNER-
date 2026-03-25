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

namespace Wolf\Controllers\Admin\Watchlist;

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminWatchlist';

    public function getPageParams(): array {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();

        $dataController = new Data();
        $users = $dataController->getImportantUsers($apiKey);

        $searchPlacholder = $this->f3->get('AdminUsers_search_placeholder');

        $pageParams = [
            'SEARCH_PLACEHOLDER' => $searchPlacholder,
            'IMPORTANT_USERS' => $users,
            'LOAD_DATATABLE' => true,
            'LOAD_UPLOT' => true,
            'LOAD_AUTOCOMPLETE' => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER' => true,
            'HTML_FILE' => 'admin/watchlist.html',
            'JS' => 'admin_watchlist.js',
        ];

        return parent::applyPageParams($pageParams);
    }
}
