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

namespace Ixnode\PhpApiVersionBundle\Utils\Output;

use Symfony\Component\Console\Formatter\NullOutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SimpleOutput
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-06-24)
 * @since 0.1.0 (2023-06-24) First version.
 */
class SimpleOutput implements OutputInterface
{
    private NullOutputFormatter $formatter;

    private string $output = '';

    /**
     * {@inheritdoc}
     */
    public function setFormatter(OutputFormatterInterface $formatter): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(): OutputFormatterInterface
    {
        // to comply with the interface we must return a OutputFormatterInterface
        return $this->formatter ??= new NullOutputFormatter();
    }

    /**
     * {@inheritdoc}
     */
    public function setDecorated(bool $decorated): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isDecorated(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setVerbosity(int $level): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity(): int
    {
        return self::VERBOSITY_QUIET;
    }

    /**
     * {@inheritdoc}
     */
    public function isQuiet(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isVeryVerbose(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     * @param string|iterable<int, string> $messages
     */
    public function writeln(string|iterable $messages, int $options = self::OUTPUT_NORMAL): void
    {
        $this->write($messages, true, $options);
    }

    /**
     * {@inheritdoc}
     * @param string|iterable<int, string> $messages
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function write(string|iterable $messages, bool $newline = false, int $options = self::OUTPUT_NORMAL): void
    {
        $messages = is_string($messages) ? [$messages] : $messages;

        foreach ($messages as $message) {
            $this->output .= (!empty($this->output) && $newline ? PHP_EOL : '').trim($message);
        }
    }

    /**
     * @return string
     */
    public function fetch(): string
    {
        return $this->output;
    }
}
