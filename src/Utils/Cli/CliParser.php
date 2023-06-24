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

use Ixnode\PhpApiVersionBundle\Tests\Unit\Utils\Cli\CliParserTest;
use Ixnode\PhpException\Case\CaseInvalidException;

/**
 * Class CliParser
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-06-24)
 * @since 0.1.0 (2023-06-24) First version.
 * @link CliParserTest
 */
class CliParser
{
    private const TAG_PATTERN = '~<\s*?%s\b[^>]*>(.*?)</%s\b[^>]*>~s';

    private const ERROR = 'error';

    private const INFO = 'info';

    /** @var array<int, string> $errorMessages */
    private array $errorMessages = [];

    /** @var array<int, string> $infoMessages */
    private array $infoMessages = [];

    private readonly string $parsed;

    /**
     * @param string $content
     * @throws CaseInvalidException
     */
    public function __construct(protected string $content)
    {
        $this->parsed = $this->parse($content);
    }

    /**
     * Replaces the first match of the needle with the given replacement.
     *
     * @param string $haystack
     * @param string $needle
     * @param string $replacement
     * @return string
     */
    private function replaceFirst(string $haystack, string $needle, string $replacement): string
    {
        $pos = strpos($haystack, $needle);

        if ($pos === false) {
            return $haystack;
        }

        return substr_replace($haystack, $replacement, $pos, strlen($needle));
    }

    /**
     * Removes given tags from the content.
     *
     * @throws CaseInvalidException
     */
    private function removeTag(string $content, string $tag): string
    {
        $matches = [];

        $pattern = sprintf(self::TAG_PATTERN, $tag, $tag);

        /* Collect and replace <error> tags. */
        while (preg_match($pattern, $content, $matches)) {
            $found = $matches[0];
            $trimmed = trim(strip_tags($matches[1]));

            if (empty($trimmed)) {
                $content = $this->replaceFirst($content, $found, '');
                continue;
            }

            match (true) {
                $tag === self::ERROR => $this->errorMessages[] = $trimmed,
                $tag === self::INFO => $this->infoMessages[] = $trimmed,
                default => throw new CaseInvalidException($tag, [self::ERROR, self::INFO, ]),
            };

            $trimmed .= str_ends_with($trimmed, '.') ? ' ' : '. ';
            $content = $this->replaceFirst($content, $found, $trimmed);
        }

        return $content;
    }

    /**
     * Parses the content.
     *
     * @param string $content
     * @return string
     * @throws CaseInvalidException
     */
    private function parse(string $content): string
    {
        /* Reset the message collector. */
        $this->errorMessages = [];
        $this->infoMessages = [];

        $content = str_replace(["\n", "\r"], '', $content);

        $content = $this->removeTag($content, self::ERROR);
        $content = $this->removeTag($content, self::INFO);

        return trim($content);
    }

    /**
     * Returns the parsed content.
     *
     * @return string
     */
    public function get(): string
    {
        return $this->parsed;
    }

    /**
     * Returns whether the class has error messages.
     *
     * @return bool
     */
    public function hasErrorMessages(): bool
    {
        return count($this->errorMessages) > 0;
    }

    /**
     * @return array<int, string>
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * Returns whether the class has info messages.
     *
     * @return bool
     */
    public function hasInfoMessages(): bool
    {
        return count($this->infoMessages) > 0;
    }

    /**
     * @return array<int, string>
     */
    public function getInfoMessages(): array
    {
        return $this->infoMessages;
    }
}
