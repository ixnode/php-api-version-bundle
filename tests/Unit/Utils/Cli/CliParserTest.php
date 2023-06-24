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

namespace Ixnode\PhpApiVersionBundle\Tests\Unit\Utils\Cli;

use Ixnode\PhpApiVersionBundle\Utils\Cli\CliParser;
use PHPUnit\Framework\TestCase;

/**
 * Class CliParserTest.
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-06-24)
 * @since 0.1.0 (2023-06-24) First version.
 * @link CliParser
 */
final class CliParserTest extends TestCase
{
    /**
     * Test wrapper (CliParser::get).
     *
     * @dataProvider dataProvider
     *
     * @test
     * @testdox $number) Test CliParser::get
     * @param int $number
     * @param string $content
     * @param string $expected
     */
    public function wrapper(int $number, string $content, string $expected): void
    {
        /* Arrange */

        /* Act */
        $parser = new CliParser($content);

        /* Assert */
        $this->assertIsNumeric($number); // To avoid phpmd warning.
        $this->assertEquals($expected, $parser->get());
    }

    /**
     * Data provider (CliParser::get).
     *
     * @return array<int, array{int, string, string}>
     * @link CheckerJson::isJson()
     */
    public function dataProvider(): array
    {
        $number = 0;

        return [
            /* Positive true tests (simple). */
            [++$number, 'Text', 'Text', ],
            [++$number, '  Text  ', 'Text', ],

            /* Positive true tests (with tags). */
            [++$number, '<error>Could not read <comment>Test</comment>.</error>', 'Could not read Test.', ],
            [++$number, '<error>Could not read <comment>Test</comment>.</error><error>Could not read <comment>Test</comment>.</error>', 'Could not read Test. Could not read Test.', ],

            /* Positive true tests (with tags). */
            [++$number, '<info>Could read <comment>Test</comment>.</info>', 'Could read Test.', ],
            [++$number, '<info>Could read <comment>Test</comment>.</info><info>Could read <comment>Test</comment>.</info>', 'Could read Test. Could read Test.', ],

            /* Positive true tests (complex). */
            [   ++$number,
                "<error>                                               </error>
<error>  Not enough arguments (missing: \"receiver\").  </error>
<error>                                               </error>

<info>mail:continue [-f|--file FILE] [-o|--output OUTPUT] [-s|--send] [-x|--force-send] [--] \<key\> \<receiver\></info>",
                'Not enough arguments (missing: "receiver"). mail:continue [-f|--file FILE] [-o|--output OUTPUT] [-s|--send] [-x|--force-send] [--] \ \.',
            ]
        ];
    }
}
