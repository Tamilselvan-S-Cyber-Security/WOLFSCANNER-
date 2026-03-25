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

namespace Wolf\Controllers\Admin\Users;

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminUsers';

    public function getPageParams(): array {
        $searchPlacholder = $this->f3->get('AdminUsers_search_placeholder');
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $rulesController = new \Wolf\Controllers\Admin\Rules\Data();

        $ruleUid = \Wolf\Utils\Conversion::getStringRequestParam('ruleUid');
        $ruleUid = $ruleUid ? strtoupper($ruleUid) : null;

        $pageParams = [
            'SEARCH_PLACEHOLDER'    => $searchPlacholder,
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'LOAD_CHOICES'          => true,
            'HTML_FILE'             => 'admin/users.html',
            'JS'                    => 'admin_users.js',
            'RULES'                 => $rulesController->getAllRulesByApiKey($apiKey),
            'DEFAULT_RULE'          => $ruleUid,
        ];

        return parent::applyPageParams($pageParams);
    }
}
