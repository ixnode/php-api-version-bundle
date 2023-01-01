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

namespace Ixnode\PhpApiVersionBundle;

use Ixnode\PhpApiVersionBundle\DependencyInjection\IxnodePhpApiVersionExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Class IxnodePhpApiVersionBundle
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
class IxnodePhpApiVersionBundle extends AbstractBundle
{
    /**
     * Returns the Extension class.
     *
     * @return Extension|null
     */
    public function getContainerExtension(): ?Extension
    {
        return new IxnodePhpApiVersionExtension();
    }
}