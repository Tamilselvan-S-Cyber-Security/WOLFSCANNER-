<?php

declare(strict_types=1);

namespace Wolf\Controllers\Admin\Vuln;

class Navigation extends \Wolf\Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = new Page();
    }
}
