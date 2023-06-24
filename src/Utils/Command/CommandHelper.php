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

namespace Ixnode\PhpApiVersionBundle\Utils\Command;

use Ixnode\PhpApiVersionBundle\Kernel;
use Exception;
use Ixnode\PhpApiVersionBundle\Utils\Cli\CliParser;
use Ixnode\PhpApiVersionBundle\Utils\Output\SimpleOutput;
use Ixnode\PhpException\Case\CaseInvalidException;
use Ixnode\PhpException\Configuration\ConfigurationMissingException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class CommandHelper
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
class CommandHelper
{
    protected const LINE_BREAK = "\n";

    protected const OUTPUT_WIDTH = 75;

    protected Application $application;

    protected bool $debug = false;

    protected string $lastCommandOutput;

    protected int $lastExecCode;

    /**
     * CommandHelper constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
    }

    /**
     * Returns the application for this test.
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @return string|null
     */
    public function getEnvironment(): ?string
    {
        return $this->application->getKernel()->getEnvironment();
    }

    /**
     * @param string $environment
     * @param bool|null $debug
     * @return self
     */
    public function setEnvironment(string $environment, ?bool $debug = null): self
    {
        if ($debug !== null) {
            $this->setDebug($debug);
        }

        $kernel = new Kernel($environment, $this->isDebug());

        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return self
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * Replaces and returns the configured commands.
     *
     * @param string[] $commands
     * @return string[]
     * @throws Exception
     */
    protected function translateCommands(array $commands): array
    {
        /* Gets the environment */
        $environment = $this->getEnvironment();

        $replaceElements = [
            '%(environment)s' => $environment,
        ];

        foreach ($commands as &$command) {
            $command = str_replace(
                array_keys($replaceElements),
                array_values($replaceElements),
                $command
            );
        }

        return $commands;
    }

    /**
     * Print and execute commands.
     *
     * @param string[] $commands
     * @return void
     * @throws Exception
     */
    public function printAndExecuteCommands(array $commands): void
    {
        /* translate the given command array. */
        $commands = $this->translateCommands($commands);

        /* Print Header */
        print self::LINE_BREAK;
        print '┏━'.$this->strRepeatUntil('━', self::OUTPUT_WIDTH).'━┓'.self::LINE_BREAK;
        print '┃ '.$this->strRepeatUntil(' ', self::OUTPUT_WIDTH, sprintf('PREPARE THE DATABASE (%s)', $this->getEnvironment())).' ┃'.self::LINE_BREAK;
        print '┣━'.$this->strRepeatUntil('━', self::OUTPUT_WIDTH).'━┫'.self::LINE_BREAK;

        /* Execute commands */
        $number = 0;
        foreach ($commands as $comment => $command) {
            if ($number > 0) {
                print '┠─'.$this->strRepeatUntil('─', self::OUTPUT_WIDTH).'─┨'."\n";
            }

            print '┃ '.$this->strRepeatUntil(' ', self::OUTPUT_WIDTH, $comment).' ┃'.self::LINE_BREAK;
            print '┃ '.$this->strRepeatUntil(' ', self::OUTPUT_WIDTH, sprintf('$ bin/console %s', $command)).' ┃'.self::LINE_BREAK;

            $message = '~ Dry Run.';

            if (!$this->isDebug()) {
                $this->runCommand($command);
                $message = '~ Done.';
            }

            print '┃ '.$this->strRepeatUntil(' ', self::OUTPUT_WIDTH, $message).' ┃'.self::LINE_BREAK;

            $number++;
        }

        /* Print Footer */
        print '┗━'.$this->strRepeatUntil('━', self::OUTPUT_WIDTH).'━┛'."\n";
        print "\n";
    }

    /**
     * Print and execute commands.
     *
     * @param array<string, string> $commands
     * @return array<int|string, mixed>
     * @throws Exception
     */
    public function returnAndExecuteCommands(array $commands): array
    {
        $commands = $this->translateCommands($commands);

        $data = [
            'header' => sprintf('Prepare the database (environment: %s)', $this->getEnvironment()),
            'command' => [],
        ];

        /* Execute commands */
        foreach ($commands as $comment => $command) {
            $dataCommand = [
                'comment' => $comment,
                'command' => sprintf('$ bin/console %s', $command),
            ];

            $message = '~ Dry Run.';

            $execCode = 0;

            if (!$this->isDebug()) {
                $execCode = $this->runCommand($command);

                $dataCommand['execCode'] = $execCode;

                $message = $execCode === 0 ? '~ Done.' : '~ Failed.';
            }

            $dataCommand['status'] = $message;

            $data['command'][] = $dataCommand;

            if ($execCode!== 0) {
                break;
            }
        }

        return $data;
    }

    /**
     * Prints the given string and fill up with char to wanted length.
     *
     * @param string $char
     * @param int $length
     * @param string $alreadyIssued
     * @return string
     */
    public function strRepeatUntil(string $char, int $length, string $alreadyIssued = ''): string
    {
        return $alreadyIssued.str_repeat($char, $length - strlen($alreadyIssued));
    }

    /**
     * Runs the given command.
     *
     * @param string $command
     * @return int
     * @throws Exception
     */
    public function runCommand(string $command): int
    {
        $command = sprintf('%s --quiet', $command);

        $output = new SimpleOutput();

        $execCode = $this->application->run(new StringInput($command), $output);

        $this->setLastExecCode($execCode);
        $this->setLastCommandOutput($output->fetch());

        return $execCode;
    }

    /**
     * Returns the last command output.
     *
     * @return string
     */
    public function getLastCommandOutput(): string
    {
        return $this->lastCommandOutput;
    }

    /**
     * Sets the last command output.
     *
     * @param string $lastCommandOutput
     * @return self
     */
    public function setLastCommandOutput(string $lastCommandOutput): self
    {
        $this->lastCommandOutput = $lastCommandOutput;

        return $this;
    }

    /**
     * Gets the last status code.
     *
     * @return int
     */
    public function getLastExecCode(): int
    {
        return $this->lastExecCode;
    }

    /**
     * Sets the last status code.
     *
     * @param int $lastExecCode
     * @return self
     */
    public function setLastExecCode(int $lastExecCode): self
    {
        $this->lastExecCode = $lastExecCode;

        return $this;
    }

    /**
     * Returns the first error message.
     *
     * @return string
     * @throws ConfigurationMissingException
     * @throws CaseInvalidException
     */
    public function getFirstError(): string
    {
        if (!isset($this->lastCommandOutput)) {
            throw new ConfigurationMissingException('The property lastCommandOutput is undefined.');
        }

        if (!isset($this->lastExecCode)) {
            throw new ConfigurationMissingException('The property lastExecCode is undefined.');
        }

        if ($this->lastExecCode === Command::SUCCESS) {
            throw new CaseInvalidException('Command::SUCCESS', ['Command::FAILURE', 'Command::INVALID']);
        }

        $cliParser = new CliParser($this->lastCommandOutput);

        if ($cliParser->hasErrorMessages()) {
            return $cliParser->getErrorMessages()[0];
        }

        return $cliParser->get();
    }
}
