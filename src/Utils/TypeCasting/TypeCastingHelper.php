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

namespace Ixnode\PhpApiVersionBundle\Utils\TypeCasting;

use Ixnode\PhpException\Type\TypeInvalidException;

/**
 * Class TypeCastingHelper.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-06-24)
 * @since 0.1.0 (2023-06-24) First version.
 */
class TypeCastingHelper
{
    /**
     * @param mixed $value
     */
    public function __construct(protected mixed $value)
    {
    }

    /**
     * Checks if the specified value can be converted to a string (with exception).
     *
     * @param string|null $default
     * @return string
     * @throws TypeInvalidException
     */
    public function strval(?string $default = null): string
    {
        if (
            is_bool($this->value) ||
            is_float($this->value) ||
            is_int($this->value) ||
            is_resource($this->value) ||
            is_string($this->value) ||
            is_null($this->value)
        ) {
            return (string) $this->value;
        }

        /* Use default if not convertible. '' (empty string) for example. */
        if (is_string($default)) {
            return $default;
        }

        throw new TypeInvalidException('string', gettype($this->value));
    }

    /**
     * Checks if the specified value can be converted to a string (without exception).
     *
     * @param string|null $default
     * @return string
     */
    public function strvalNE(?string $default = null): string
    {
        $default = $default ?: '';

        try {
            return $this->strval($default);
        } catch (TypeInvalidException) {
            return $default;
        }
    }

    /**
     * Checks if the specified value can be converted to an integer (with exception).
     *
     * @param int|null $default
     * @return int
     * @throws TypeInvalidException
     */
    public function intval(?int $default = null): int
    {
        if (
            is_array($this->value) ||
            is_bool($this->value) ||
            is_float($this->value) ||
            is_int($this->value) ||
            is_resource($this->value) ||
            is_string($this->value) ||
            is_null($this->value)
        ) {
            return (int) $this->value;
        }

        /* Use default if not convertible. '' (empty string) for example. */
        if (is_int($default)) {
            return $default;
        }

        throw new TypeInvalidException('int', gettype($this->value));
    }

    /**
     * Checks if the specified value can be converted to an integer (without exception).
     *
     * @param int|null $default
     * @return int
     */
    public function intvalNE(?int $default = null): int
    {
        $default = $default ?: 0;

        try {
            return $this->intval($default);
        } catch (TypeInvalidException) {
            return $default;
        }
    }

    /**
     * Checks if the specified value can be converted to a float (with exception).
     *
     * @param float|null $default
     * @return float
     * @throws TypeInvalidException
     */
    public function floatval(?float $default = null): float
    {
        if (
            is_array($this->value) ||
            is_bool($this->value) ||
            is_float($this->value) ||
            is_int($this->value) ||
            is_string($this->value) ||
            is_null($this->value)
        ) {
            return (float) $this->value;
        }

        /* Use default if not convertible. '' (empty string) for example. */
        if (is_float($default)) {
            return $default;
        }

        throw new TypeInvalidException('float', gettype($this->value));
    }

    /**
     * Checks if the specified value can be converted to a float (without exception).
     *
     * @param float|null $default
     * @return float
     */
    public function floatvalNE(?float $default = null): float
    {
        $default = $default ?: 0;

        try {
            return $this->floatval($default);
        } catch (TypeInvalidException) {
            return $default;
        }
    }
}
