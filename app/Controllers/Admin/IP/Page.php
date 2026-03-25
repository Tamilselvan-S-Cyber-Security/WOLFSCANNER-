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

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminIp';

    public function getPageParams(): array {
        $dataController = new Data();
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $ipId = \Wolf\Utils\Conversion::getIntUrlParam('ipId');
        $hasAccess = $dataController->checkIfOperatorHasAccess($ipId, $apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $ipAddr = $dataController->getFullIpInfoById($ipId, $apiKey);
        $pageTitle = $this->getInternalPageTitleWithPostfix($ipAddr['ip']);
        $isEnrichable = $dataController->isEnrichable($apiKey);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/ip.html',
            'PAGE_TITLE'                    => $pageTitle,
            'IP'                            => $ipAddr,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'JS'                            => 'admin_ip.js',
            'IS_ENRICHABLE'                 => $isEnrichable,
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
            // recall ip data
            $pageParams['IP'] = $dataController->getFullIpInfoById($ipId, $apiKey);
        }

        return parent::applyPageParams($pageParams);
    }
}
