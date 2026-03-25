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

namespace Wolf\Controllers\Admin\Events;

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminEvents';

    public function getPageParams(): array {
        $searchPlacholder = $this->f3->get('AdminEvents_search_placeholder');
        $controller = new Data();
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $rulesController = new \Wolf\Controllers\Admin\Rules\Data();

        $pageParams = [
            'SEARCH_PLACEHOLDER'            => $searchPlacholder,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_DATATABLE'                => true,
            'LOAD_CHOICES'                  => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/events.html',
            'JS'                            => 'admin_events.js',
            'EVENT_TYPES'                   => $controller->getAllEventTypes(),
            'DEVICE_TYPES'                  => $controller->getAllDeviceTypes(),
            'RULES'                         => $rulesController->getAllRulesByApiKey($apiKey),
        ];

        return parent::applyPageParams($pageParams);
    }
}
