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

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\Operation;

use ApiPlatform\Operation\PathSegmentNameGeneratorInterface;
use Ixnode\PhpException\Function\FunctionReplaceException;

/**
 * Class SingularPathSegmentNameGenerator
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
final class SingularPathSegmentNameGenerator implements PathSegmentNameGeneratorInterface
{
    /**
     * Returns the segment name.
     *
     * @param string $name
     * @param bool $collection
     * @return string
     * @throws FunctionReplaceException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getSegmentName(string $name, bool $collection = true): string
    {
        return $this->dashize($name);
    }

    /**
     * Returns the singular and dashed route name.
     *
     * @param string $string
     * @return string
     * @throws FunctionReplaceException
     */
    private function dashize(string $string): string
    {
        $replacePattern = '~(?<=\\w)([A-Z])~';

        $dashized = preg_replace($replacePattern, '-$1', $string);

        if (!is_string($dashized)) {
            throw new FunctionReplaceException($replacePattern);
        }

        return strtolower($dashized);
    }
}
