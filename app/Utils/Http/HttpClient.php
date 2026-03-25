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

namespace Wolf\Utils\Http;

class HttpClient {
    /** @var array<int, \Wolf\Interfaces\HttpTransportInterface> */
    private array $transports;

    /**
     * @param array<int, \Wolf\Interfaces\HttpTransportInterface> $transports
     */
    public function __construct(array $transports) {
        $this->transports = $transports;
    }

    public static function default(): self {
        $transports = [
            new \Wolf\Utils\Http\CurlTransport(),
            new \Wolf\Utils\Http\StreamTransport(),
        ];

        return new self($transports);
    }

    public function request(\Wolf\Entities\HttpRequest $request): \Wolf\Entities\HttpResponse {
        foreach ($this->transports as $transport) {
            if ($transport->isAvailable()) {
                return $transport->request($request);
            }
        }

        return \Wolf\Entities\HttpResponse::failure(null, 'no_transport_available', []);
    }
}
