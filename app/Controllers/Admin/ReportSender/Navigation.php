<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\ReportSender;

class Navigation extends \Wolf\Controllers\Admin\Base\Navigation
{
    public function __construct()
    {
        parent::__construct();

        $this->page = new Page();
    }
}
