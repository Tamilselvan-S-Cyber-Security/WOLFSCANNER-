<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\Debugger;

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminDebugger';

    public function getPageParams(): array {
        $dataController = new Data();

        $pageParams = [
            'HTML_FILE' => 'admin/debugger.html',
            'PAGE_TITLE' => 'Debugger',
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();
            $pageParams = array_merge($pageParams, $operationResponse);
        }

        return parent::applyPageParams($pageParams);
    }
}
