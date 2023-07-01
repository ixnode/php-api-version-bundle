<?php

declare(strict_types=1);

/*
 * This file is part of the ixnode/php-api-version-bundle project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\Route\Base;

/**
 * Class BaseRoute
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
abstract class BaseRoute
{
    final public const KEY_REQUEST = 'key-request';

    final public const KEY_RESPONSE = 'key-response';

    final public const KEY_DEFAULT = 'default';

    final public const KEY_TYPE = 'type';

    final public const TYPE_BOOLEAN = 'boolean';

    final public const TYPE_STRING = 'string';

    final public const TYPE_ENUM_STRING = 'string';

    final public const TYPE_INTEGER = 'integer';
}
