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

namespace Ixnode\PhpApiVersionBundle\Command\Base;

use Ixnode\PhpApiVersionBundle\Utils\Db\Entity;
use Ixnode\PhpApiVersionBundle\Utils\Db\Repository;
use Exception;
use Ixnode\PhpApiVersionBundle\Utils\TypeCasting\TypeCastingHelper;
use Ixnode\PhpContainer\Json;
use Ixnode\PhpException\Case\CaseInvalidException;
use Ixnode\PhpException\File\FileNotFoundException;
use Ixnode\PhpException\File\FileNotReadableException;
use Ixnode\PhpException\Function\FunctionJsonEncodeException;
use Ixnode\PhpException\Type\TypeInvalidException;
use Ixnode\PhpNamingConventions\Exception\FunctionReplaceException;
use Ixnode\PhpNamingConventions\NamingConventions;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class BaseCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
abstract class BaseCommand extends Command
{
    protected const NAME_OPTION_FORMAT = 'format';

    protected const NAME_OPTION_FORMAT_SHORT = 'f';

    protected const OPTION_FORMAT_TEXT = 'text';

    protected const OPTION_FORMAT_JSON = 'json';

    protected const TEMPLATE_PRINT = '%-40s %s';

    protected const MESSAGE_SUCCESS = 'The result is delivered in the data part.';

    protected const KEY_ERROR = 'error';

    protected InputInterface $input;

    protected OutputInterface $output;

    /**
     * BaseCommand constructor.
     *
     * @param Entity $entity
     * @param Repository $repository
     */
    public function __construct(protected Entity $entity, protected Repository $repository)
    {
        parent::__construct();
    }

    /**
     * Configures the command (default).
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addOption(self::NAME_OPTION_FORMAT, self::NAME_OPTION_FORMAT_SHORT, InputOption::VALUE_REQUIRED, 'Output format.', self::OPTION_FORMAT_JSON);

        $this->configureCommand();
    }

    /**
     * Execute the command (single command).
     *
     * @return array<int|string, mixed>
     * @throws Exception
     */
    abstract protected function executeCommand(): array;

    /**
     * Configures the command (single command).
     *
     * @return void
     */
    abstract protected function configureCommand(): void;

    /**
     * Gets the value.
     *
     * @param mixed $value
     * @return string
     * @throws TypeInvalidException
     */
    protected function getValue(mixed $value): string
    {
        return match (true) {
            is_string($value) => $value,
            is_bool($value) => $value ? 'Yes' : 'No',
            default => (new TypeCastingHelper($value))->strval(),
        };
    }

    /**
     * Gets the key.
     *
     * @param mixed $key
     * @param string|null $recursiveName
     * @return string
     * @throws TypeInvalidException
     */
    protected function getKey(mixed $key, ?string $recursiveName): string
    {
        if (is_numeric($key)) {
            $key = sprintf('%d:', $key);
        }

        $key = (new TypeCastingHelper($key))->strval();

        if ($recursiveName === null) {
            return $key;
        }

        return sprintf('%s_%s', $recursiveName, $key);
    }

    /**
     * Prints the version array as text.
     *
     * @param OutputInterface $output
     * @param array<int|string, mixed> $outputArray
     * @param string|null $recursiveName
     * @return void
     * @throws FunctionReplaceException
     */
    protected function printText(OutputInterface $output, array $outputArray, ?string $recursiveName = null): void
    {
        if ($recursiveName === null) {
            $output->writeln('');
        }

        foreach ($outputArray as $key => $value) {
            $newKey = $this->getKey($key, $recursiveName);

            if (is_array($value)) {
                if ($recursiveName !== null) {
                    $output->writeln('');
                }
                $this->printText($output, $value, $newKey);
                continue;
            }

            $title = (new NamingConventions($newKey))->getTitle();
            $output->writeln(sprintf(self::TEMPLATE_PRINT, sprintf('  %s:', $title), $this->getValue($value)));
        }

        if ($recursiveName === null) {
            $output->writeln('');
        }
    }

    /**
     * Prints the version array as json.
     *
     * @param OutputInterface $output
     * @param array<int|string, mixed> $array
     * @return void
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     * @throws TypeInvalidException
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     */
    protected function printJson(OutputInterface $output, array $array): void
    {
        $output->writeln((new Json($array))->getJsonStringFormatted());
    }

    /**
     * @param array<int|string, mixed> $data
     * @param array<int|string, mixed>|null $extra
     * @return array<int|string, mixed>
     */
    protected function getSuccessArray(array $data, array $extra = null): array
    {
        $return = [
            'valid' => true,
            'message' => self::MESSAGE_SUCCESS,
        ];

        if ($extra !== null) {
            $return = array_merge($return, $extra);
        }

        $return['data'] = $data;

        return $return;
    }

    /**
     * @param string $message
     * @return array<int|string, mixed>
     */
    protected function getEmptyArray(string $message): array
    {
        return [
            'valid' => false,
            'message' => $message,
            'data' => [],
        ];
    }

    /**
     * Execute the command (default).
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     * @throws FunctionReplaceException
     * @throws CaseInvalidException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $format = (new TypeCastingHelper($this->input->getOption(self::NAME_OPTION_FORMAT)))->strval();

        match ($format) {
            self::OPTION_FORMAT_TEXT => $this->printText($output, $this->executeCommand()),
            self::OPTION_FORMAT_JSON => $this->printJson($output, $this->executeCommand()),
            default => throw new CaseInvalidException($format, [self::OPTION_FORMAT_TEXT, self::OPTION_FORMAT_JSON]),
        };

        return Command::SUCCESS;
    }

    /**
     * Returns the repository helper.
     *
     * @return Repository
     */
    protected function getRepository(): Repository
    {
        return $this->repository;
    }

    /**
     * Returns the entity helper.
     *
     * @return Entity
     */
    protected function getEntity(): Entity
    {
        return $this->entity;
    }
}
