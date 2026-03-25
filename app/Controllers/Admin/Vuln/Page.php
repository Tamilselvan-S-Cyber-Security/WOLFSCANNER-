<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\Vuln;

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminVuln';

    public function getPageParams(): array {
        $dataController = new Data();

        $pageParams = [
            'HTML_FILE' => 'admin/vuln.html',
            'PAGE_TITLE' => 'Auto Scanner',
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();
            $pageParams = array_merge($pageParams, $operationResponse);
        }

        return parent::applyPageParams($pageParams);
    }
}
