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

namespace Wolf\Controllers;

class Navigation extends Base {
    public \Wolf\Views\Base $response;

    public function beforeroute(): void {
        // CSRF assignment in base page
        $this->response = new \Wolf\Views\Frontend();
    }

    /**
     * kick start the View, which creates the response
     * based on our previously set content data.
     * finally echo the response or overwrite this method
     * and do something else with it.
     */
    public function afterroute(): void {
        echo $this->response->render();
    }

    public function visitSignupPage(): void {
        \Wolf\Utils\Routes::redirectIfLogged();

        $pageController = new \Wolf\Controllers\Pages\Signup();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitLoginPage(): void {
        \Wolf\Utils\Routes::redirectIfLogged();

        $pageController = new \Wolf\Controllers\Pages\Login();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitForgotPasswordPage(): void {
        \Wolf\Utils\Routes::redirectIfLogged();

        if (!\Wolf\Utils\Variables::getForgotPasswordAllowed()) {
            $this->f3->reroute('/');
        }

        $pageController = new \Wolf\Controllers\Pages\ForgotPassword();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitPasswordRecoveringPage(): void {
        \Wolf\Utils\Routes::redirectIfLogged();

        $pageController = new \Wolf\Controllers\Pages\PasswordRecovering();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitLogoutPage(): void {
        \Wolf\Utils\Routes::redirectIfUnlogged();

        $pageController = new \Wolf\Controllers\Pages\Logout();
        $this->response->data = $pageController->getPageParams();
    }
}
