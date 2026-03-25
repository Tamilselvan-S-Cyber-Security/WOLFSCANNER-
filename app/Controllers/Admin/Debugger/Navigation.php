<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\Debugger;

class Navigation extends \Wolf\Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = new Page();
    }

    public function downloadZip(): void {
        \Wolf\Utils\Routes::redirectIfUnlogged();

        $errorCode = $this->validateCsrfToken();
        if ($errorCode) {
            $this->f3->error(403);
        }

        $last = $this->f3->get('SESSION.debugger_last');
        if (!is_array($last) || !isset($last['html']) || !isset($last['report'])) {
            $this->f3->error(404);
        }

        if (!class_exists('\ZipArchive')) {
            $this->f3->error(500);
        }

        $zip = new \ZipArchive();
        $tmp = tempnam(sys_get_temp_dir(), 'wolf_dbg_');
        if ($tmp === false) {
            $this->f3->error(500);
        }

        $zipPath = $tmp . '.zip';
        @unlink($zipPath);

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            $this->f3->error(500);
        }

        $zip->addFromString('source.html', (string)$last['html']);
        $zip->addFromString('report.json', json_encode($last['report'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $zip->close();

        $downloadName = 'website-debug.zip';
        if (isset($last['report']['host']) && is_string($last['report']['host']) && $last['report']['host'] !== '') {
            $downloadName = preg_replace('~[^a-z0-9_.-]+~i', '_', $last['report']['host']) . '.zip';
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($zipPath));

        readfile($zipPath);

        @unlink($tmp);
        @unlink($zipPath);
        exit;
    }
}
