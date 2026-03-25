<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\ReportSender;

class Page extends \Wolf\Controllers\Admin\Base\Page
{
    public ?string $page = 'AdminReportSender';

    public function getPageParams(): array
    {
        $apiKey = \Wolf\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $totalSent = \Wolf\Utils\ReportSenderCount::getCount($apiKey);

        $pageParams = [
            'HTML_FILE' => 'admin/report-sender.html',
            'PAGE_TITLE' => 'Report Sender',
            'LOAD_EMAILJS' => true,
            'REPORT_SENDER_TOTAL_COUNT' => $totalSent,
        ];

        return parent::applyPageParams($pageParams);
    }
}
