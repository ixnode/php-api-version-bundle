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

namespace Ixnode\PhpApiVersionBundle\Command\Db;

use Exception;
use Ixnode\PhpApiVersionBundle\Command\Base\BaseCommand;
use Ixnode\PhpApiVersionBundle\Constants\Command\CommandData;
use Ixnode\PhpApiVersionBundle\Utils\Command\CommandHelper;
use Ixnode\PhpApiVersionBundle\Utils\Db\Entity;
use Ixnode\PhpApiVersionBundle\Utils\Db\Repository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReinitializeCommand
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 * @example bin/console db:reinitialize
 */
#[AsCommand(
    name: self::COMMAND_NAME,
    description: self::COMMAND_DESCRIPTION
)]
class ReinitializeCommand extends BaseCommand
{
    final public const COMMAND_NAME = 'db:reinitialize';

    final public const COMMAND_DESCRIPTION = 'Reinitialize the test or prod db.';

    final protected const NAME_OPTION_SCHEMA_DROP = 'schema-drop';

    final protected const NAME_OPTION_SCHEMA_DROP_SHORT = 'd';

    final protected const NAME_OPTION_CREATE_SCHEMA = 'create-schema';

    final protected const NAME_OPTION_CREATE_SCHEMA_SHORT = 'c';

    final protected const NAME_OPTION_LOAD_FIXTURES = 'load-fixtures';

    final protected const NAME_OPTION_LOAD_FIXTURES_SHORT = 'l';

    final protected const NAME_OPTION_ENVIRONMENT = 'env';

    /**
     * ReinitializeCommand constructor.
     *
     * @param Entity $entity
     * @param Repository $repository
     * @param CommandHelper $commandHelper
     */
    public function __construct(protected Entity $entity, protected Repository $repository, protected CommandHelper $commandHelper)
    {
        parent::__construct($this->entity, $this->repository);
    }

    /**
     * @return void
     */
    protected function configureCommand(): void
    {
        $this
            ->addUsage('--no-schema-drop --no-create-schema')
            ->addOption(self::NAME_OPTION_SCHEMA_DROP, self::NAME_OPTION_SCHEMA_DROP_SHORT, InputOption::VALUE_NEGATABLE, 'Enable or disable dropping the DB schema.', true)
            ->addOption(self::NAME_OPTION_CREATE_SCHEMA, self::NAME_OPTION_CREATE_SCHEMA_SHORT, InputOption::VALUE_NEGATABLE, 'Enable or disable creating the DB schema.', true)
            ->addOption(self::NAME_OPTION_LOAD_FIXTURES, self::NAME_OPTION_LOAD_FIXTURES_SHORT, InputOption::VALUE_NEGATABLE, 'Enable or disable loading fixtures into the DB.', true)
        ;
    }

    /**
     * Execute the command (single command).
     *
     * @return array<int|string, mixed>
     * @throws Exception
     */
    protected function executeCommand(): array
    {
        $schemaDrop = boolval($this->input->getOption(self::NAME_OPTION_SCHEMA_DROP));
        $schemaCreate = boolval($this->input->getOption(self::NAME_OPTION_CREATE_SCHEMA));
        $fixturesLoad = boolval($this->input->getOption(self::NAME_OPTION_LOAD_FIXTURES));

        $hasEnvironment = $this->input->hasParameterOption(sprintf('--%s', self::NAME_OPTION_ENVIRONMENT));

        if (!$hasEnvironment) {
            throw new Exception('It is not allowed to use this command without given environment. Additionally use the --env test option..');
        }

        $commands = [];

        if ($schemaDrop) {
            $commands["/* Drop schema */"] = CommandData::COMMAND_SCHEMA_DROP;
        }

        if ($schemaCreate) {
            $commands["/* Create schema */"] = CommandData::COMMAND_SCHEMA_CREATE;
        }

        if ($fixturesLoad) {
            $commands["/* Load fixtures */"] = CommandData::COMMAND_LOAD_FIXTURES;
        }

        $data = $this->commandHelper->returnAndExecuteCommands($commands);

        $lastStatusCode = $this->commandHelper->getLastExecCode();

        if ($lastStatusCode!== 0) {
            $lastCommandOutput = $this->commandHelper->getLastCommandOutput();

            $data[self::KEY_ERROR] = $lastCommandOutput;
        }

        return $data;
    }
}
