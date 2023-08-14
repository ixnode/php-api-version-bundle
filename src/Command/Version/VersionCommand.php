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

namespace Ixnode\PhpApiVersionBundle\Command\Version;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ixnode\PhpApiVersionBundle\Utils\TypeCasting\TypeCastingHelper;
use Ixnode\PhpApiVersionBundle\Utils\Version\Version;
use Ixnode\PhpContainer\Json;
use Ixnode\PhpException\Case\CaseInvalidException;
use Ixnode\PhpException\File\FileNotFoundException;
use Ixnode\PhpException\File\FileNotReadableException;
use Ixnode\PhpException\Function\FunctionJsonEncodeException;
use Ixnode\PhpException\Type\TypeInvalidException;
use Ixnode\PhpNamingConventions\NamingConventions;
use JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class VersionCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 * @example bin/console version:show
 */
#[AsCommand(
    name: self::COMMAND_NAME,
    description: self::COMMAND_DESCRIPTION
)]
class VersionCommand extends Command
{
    final public const COMMAND_NAME = 'version:show';

    final public const COMMAND_DESCRIPTION = 'Shows the version of this app.';

    protected const NAME_OPTION_FORMAT = 'format';

    protected const NAME_OPTION_FORMAT_SHORT = 'f';

    protected const OPTION_FORMAT_TEXT = 'text';

    protected const OPTION_FORMAT_JSON = 'json';

    protected const KEY_NAME = 'name';

    protected const KEY_DESCRIPTION = 'description';

    protected const KEY_VERSION = 'version';

    protected const KEY_DATE = 'date';

    protected const KEY_LICENSE = 'license';

    protected const KEY_AUTHORS = 'authors';

    protected const KEY_PHP = 'php-version';

    protected const KEY_SYMFONY = 'symfony-version';

    protected const KEY_COMPOSER = 'composer-version';

    protected const KEY_DOCTRINE = 'doctrine-version';

    protected const KEY_API_PLATFORM = 'api-platform-version';

    protected const KEY_DRIVER_NAME = 'driver-name';

    protected const KEY_ENVIRONMENT = 'environment';

    /**
     * VersionCommand constructor.
     *
     */
    public function __construct(
        protected Version $version,
        protected KernelInterface $kernel,
        protected EntityManagerInterface $entityManager
    )
    {
        parent::__construct();
    }

    /**
     * Configures the command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addOption(
                self::NAME_OPTION_FORMAT,
                self::NAME_OPTION_FORMAT_SHORT,
                InputOption::VALUE_REQUIRED,
                'Output format.',
                self::OPTION_FORMAT_TEXT
            )
        ;
    }

    /**
     * Returns the version array.
     *
     * @return array{version: string, license: string, authors: string[], php-version: string, symfony-version: string}
     * @throws Exception
     */
    protected function getVersionArray(): array
    {
        return [
            self::KEY_NAME => $this->version->getName(),
            self::KEY_DESCRIPTION => $this->version->getDescription(),
            self::KEY_VERSION => $this->version->getVersion(),
            self::KEY_DATE => $this->version->getDate(),
            self::KEY_LICENSE => $this->version->getLicense(),
            self::KEY_AUTHORS => $this->version->getAuthors(),
            self::KEY_DRIVER_NAME => $this->version->getDriverName($this->entityManager),
            self::KEY_ENVIRONMENT => $this->version->getEnvironment($this->kernel),
            self::KEY_PHP => $this->version->getVersionPhp(),
            self::KEY_SYMFONY => $this->version->getVersionSymfony(),
            self::KEY_COMPOSER => $this->version->getVersionComposer(),
            self::KEY_DOCTRINE => $this->version->getVersionComposerPackage('doctrine/orm'),
            self::KEY_API_PLATFORM => $this->version->getVersionComposerPackage('api-platform/core'),
        ];
    }

    /**
     * Prints the version array as text.
     *
     * @param OutputInterface $output
     * @param array{version: string, license: string, authors: string[], php-version: string, symfony-version: string} $versionArray
     * @return void
     * @throws Exception
     */
    protected function printText(OutputInterface $output, array $versionArray): void
    {
        $templateFormat = '%-25s %s';

        $output->writeln('');
        foreach ($versionArray as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $title = (new NamingConventions($key))->getTitle();
                    $output->writeln(sprintf($templateFormat, sprintf('  %s:', $title), $item));
                }
                continue;
            }

            $title = (new NamingConventions($key))->getTitle();
            $title = str_replace('Php', 'PHP', $title);
            $output->writeln(sprintf($templateFormat, sprintf('  %s:', $title), $value));
        }
        $output->writeln('');
    }

    /**
     * Prints the version array as json.
     *
     * @param OutputInterface $output
     * @param array{version: string, license: string, authors: string[], php-version: string, symfony-version: string} $versionArray
     * @return void
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     * @throws TypeInvalidException
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     */
    protected function printJson(OutputInterface $output, array $versionArray): void
    {
        $output->writeln((new Json($versionArray))->getJsonStringFormatted());
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws CaseInvalidException
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $format = (new TypeCastingHelper($input->getOption(self::NAME_OPTION_FORMAT)))->strval();

        match ($format) {
            self::OPTION_FORMAT_TEXT => $this->printText($output, $this->getVersionArray()),
            self::OPTION_FORMAT_JSON => $this->printJson($output, $this->getVersionArray()),
            default => throw new CaseInvalidException($format, [self::OPTION_FORMAT_TEXT, self::OPTION_FORMAT_JSON]),
        };

        return Command::INVALID;
    }
}
