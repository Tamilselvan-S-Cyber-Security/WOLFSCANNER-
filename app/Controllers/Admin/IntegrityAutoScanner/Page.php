<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\IntegrityAutoScanner;

class Page extends \Wolf\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminIntegrityAutoScanner';

    public function getPageParams(): array {
        $dataController = new Data();

        $path = (string)$this->f3->get('PATH');
        $isVirusScan = str_contains($path, '/virus-scan');
        $base = (string)$this->f3->get('BASE');

        $pageParams = [
            'LOAD_AUTOCOMPLETE' => true,
            'LOAD_DATATABLE' => true,
            'HTML_FILE' => 'admin/integrityAutoScanner.html',
            'JS' => 'admin_integrity_auto_scanner.js',
            'PAGE_TITLE' => $isVirusScan ? 'Virus scan' : 'Integrity Auto Scanner',
            'BREADCRUMB_TITLE' => $isVirusScan ? 'Virus scan' : null,
            'PAGE_HEADING' => $isVirusScan ? 'Virus scan' : 'Integrity Auto Scanner',
            'SCAN_FORM_ACTION' => $base . ($isVirusScan ? '/virus-scan' : '/integrity-auto-scanner'),
        ];
        if (!$isVirusScan) {
            unset($pageParams['BREADCRUMB_TITLE']);
        }

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();
            $pageParams = array_merge($pageParams, $operationResponse);
        }

        return parent::applyPageParams($pageParams);
    }
}
