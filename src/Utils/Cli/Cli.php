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

namespace Ixnode\PhpApiVersionBundle\Utils\Cli;

use Ixnode\PhpException\Function\FunctionFOpenException;

/**
 * Class Cli
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-06-24)
 * @since 0.1.0 (2023-06-24) First version.
 */
class Cli
{
    /**
     * Returns the STDIN if given.
     *
     * @return ?string
     * @throws FunctionFOpenException
     */
    public function getStdin(): ?string
    {
        $fileHandler = fopen('php://stdin', 'r');

        if ($fileHandler === false) {
            throw new FunctionFOpenException();
        }

        stream_set_blocking($fileHandler, false);

        $stdin = fgets($fileHandler);

        if ($stdin === false) {
            return null;
        }

        return $stdin;
    }
}
