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

namespace Wolf\Utils\Assets\Lists;

class UserAgent extends Base {
    protected static string $extensionFile = 'user-agent.php';

    protected static array $list = [
        '--',
        '/*',
        '*/',
        'pg_',
        '\');',     // should be %'%)%;% ?
        'alter ',
        'select',
        'waitfor',
        'delay',
        'delete',
        'drop',
        'dbcc',
        'schema',
        'exists',
        'cmdshell',
        '%2A',      // *
        '%27',      // '
        '%22',      // "
        '%2D',      // -
        '%2F',      // /
        '%5C',      // \
        '%3B',      // ;
        '%23',      // #
        '%2B',      // +
        '%3D',      // =
        '%28',      // (
        '%29',      // )
        '/bin',
        '%2Fbin',
        '.sh',
        '|sh',
        '.exe',
    ];
}
