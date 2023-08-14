<?php

/*
 * This file is part of the ixnode/php-api-version-bundle project.
 *
 * (c) Björn Hempel <https://www.hempel.li/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ixnode\PhpApiVersionBundle\Constants\Code;

/**
 * Class Environments
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-08-14)
 * @since 0.1.0 (2023-08-14) First version.
 */
class Environments
{
    final public const DEVELOPMENT = 'dev';

    final public const DEVELOPMENT_NAME = 'Development environment';

    final public const STAGING = 'staging';

    final public const STAGING_NAME = 'Staging environment';

    final public const PRODUCTION = 'prod';

    final public const PRODUCTION_NAME = 'Production environment';

    final public const TEST = 'test';

    final public const TEST_NAME = 'Test';
}
