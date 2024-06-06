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

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\Route;

use Ixnode\PhpApiVersionBundle\ApiPlatform\Route\Base\BaseRoute;

/**
 * Class VersionRoute
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
final class VersionRoute extends BaseRoute
{
    final public const PROPERTIES = [];

    public const SUMMARY_GET = 'Retrieves a Version resource';

    public const DESCRIPTION_GET = 'This endpoint is used to obtain the versions of this application.';
}
