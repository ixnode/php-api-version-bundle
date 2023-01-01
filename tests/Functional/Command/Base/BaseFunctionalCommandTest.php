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

namespace Ixnode\PhpApiVersionBundle\Tests\Functional\Command\Base;

use Closure;
use Exception;
use Ixnode\PhpApiVersionBundle\Constants\Command\CommandData;
use Ixnode\PhpApiVersionBundle\Utils\Command\CommandHelper;
use Ixnode\PhpApiVersionBundle\Utils\Db\Entity;
use Ixnode\PhpApiVersionBundle\Utils\Db\Repository;
use Ixnode\PhpChecker\Checker;
use Ixnode\PhpContainer\File;
use Ixnode\PhpException\ArrayType\ArrayKeyNotFoundException;
use Ixnode\PhpException\Class\ClassInvalidException;
use Ixnode\PhpException\Configuration\ConfigurationMissingException;
use Ixnode\PhpException\File\FileNotFoundException;
use Ixnode\PhpException\Type\TypeInvalidException;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

/**
 * Class BaseCommandTest
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
abstract class BaseFunctionalCommandTest extends WebTestCase
{
    final public const MESSAGE_JSON_RESPONSE_INVALID = 'The returned json command value does not match with the given schema.';

    final public const MESSAGE_JSON_RESPONSE_VALID = 'The returned json command value unexpectedly matches the specified scheme.';

    final protected const PATH_SQLITE_DB = 'var/app.db';

    final protected const NAME_KERNEL_PROJECT_DIR = 'kernel.project_dir';

    protected CommandTester $commandTester;

    protected ParameterBagInterface $parameterBag;

    protected Entity $entity;

    protected Repository $repository;

    protected Environment $twig;

    protected CommandHelper $commandHelper;

    protected bool $useKernel = false;

    protected bool $useCommand = false;

    protected bool $useDb = false;

    protected bool $useParameterBag = false;

    protected bool $useTwig = false;

    protected bool $loadFixtures = false;

    protected bool $forceLoadFixtures = false;

    protected string $commandName;

    /** @var class-string $commandClass */
    protected string $commandClass;

    protected ?Closure $commandClassParameterClosure = null;

    abstract public function doConfig(): void;

    /**
     * @return self
     */
    protected function setConfigUseKernel(): self
    {
        $this->useKernel = true;

        return $this;
    }

    /**
     * @param string $commandName
     * @param class-string $commandClass
     * @param Closure|null $commandClassParameterClosure
     * @return self
     */
    protected function setConfigUseCommand(string $commandName, string $commandClass, ?Closure $commandClassParameterClosure = null): self
    {
        $this->setConfigUseKernel();
        $this->useCommand = true;
        $this->commandName = $commandName;
        $this->commandClass = $commandClass;
        $this->commandClassParameterClosure = $commandClassParameterClosure;

        return $this;
    }

    /**
     * @return self
     */
    protected function setConfigUseDb(): self
    {
        $this->setConfigUseKernel();
        $this->useDb = true;

        return $this;
    }

    /**
     * @return self
     */
    protected function setConfigUseParameterBag(): self
    {
        $this->setConfigUseKernel();
        $this->useParameterBag = true;

        return $this;
    }

    /**
     * @return self
     */
    protected function setConfigLoadFixtures(): self
    {
        $this->setConfigUseDb();
        $this->loadFixtures = true;

        return $this;
    }

    /**
     * @return self
     */
    protected function setConfigForceLoadFixtures(): self
    {
        $this->forceLoadFixtures = true;

        return $this;
    }

    /**
     * @return self
     */
    protected function setConfigUseTwig(): self
    {
        $this->useTwig = true;

        return $this;
    }

    /**
     * Sets up the test case.
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->doConfig();

        if ($this->useKernel) {
            self::bootKernel();
        }

        if ($this->useParameterBag) {
            $this->createService(ParameterBagInterface::class);
        }

        if ($this->useDb) {
            $this->createService(Entity::class);
            $this->createService(Repository::class);
        }

        if ($this->loadFixtures) {
            $this->createService(CommandHelper::class);
            $this->loadFixtures();
        }

        if ($this->useCommand) {
            $this->createCommand($this->commandName, $this->commandClass, $this->commandClassParameterClosure);
        }

        if ($this->useTwig) {
            $this->createService(Environment::class);
        }
    }

    /**
     * Creates a service from given class string.
     *
     * @param class-string $serviceName
     * @return void
     * @throws ClassInvalidException
     * @throws TypeInvalidException
     * @throws Exception
     */
    protected function createService(string $serviceName): void
    {
        $container = self::getContainer();

        $service = $container->get($serviceName);

        if (is_null($service)) {
            throw new TypeInvalidException('object', 'null');
        }

        match (true) {
            $service instanceof CommandHelper => $this->commandHelper = $service,
            $service instanceof Entity => $this->entity = $service,
            $service instanceof Environment => $this->twig = $service,
            $service instanceof ParameterBagInterface => $this->parameterBag = $service,
            $service instanceof Repository => $this->repository = $service,
            default => throw new ClassInvalidException($service::class, [
                CommandHelper::class,
                Entity::class,
                Environment::class,
                ParameterBagInterface::class,
                Repository::class,
            ]),
        };
    }

    /**
     * Creates the command.
     *
     * @param string $commandName
     * @param class-string $commandClass
     * @param Closure|null $commandClassParameterClosure
     * @return void
     * @throws TypeInvalidException
     * @throws ReflectionException
     * @throws ClassInvalidException
     */
    protected function createCommand(string $commandName, string $commandClass, ?Closure $commandClassParameterClosure): void
    {
        $application = new Application();

        $reflectionClass = new ReflectionClass($commandClass);

        $commandClassParameter = [];

        if ($commandClassParameterClosure !== null) {
            $commandClassParameter = (new Checker($commandClassParameterClosure->call($this)))->checkArraySimple();
        }

        $keyCommand = $reflectionClass->newInstanceArgs($commandClassParameter);

        if (!$keyCommand instanceof Command) {
            throw new ClassInvalidException($keyCommand::class, Command::class);
        }

        $application->add($keyCommand);
        $command = $application->find($commandName);

        $this->commandTester = new CommandTester($command);
    }

    /**
     * Loads all fixtures.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws Exception
     */
    protected function loadFixtures(): void
    {
        $pathSqlite = new File(self::PATH_SQLITE_DB);

        /* Skip if db already exists */
        if (!$this->forceLoadFixtures && $pathSqlite->exist() && $pathSqlite->getFileSize() > 0) {
            return;
        }

        /* Empty test table */
        $this->commandHelper->printAndExecuteCommands([
            '/* Drop schema */' => CommandData::COMMAND_SCHEMA_DROP,
            '/* Create schema */' => CommandData::COMMAND_SCHEMA_CREATE,
            '/* Load fixtures */' => CommandData::COMMAND_LOAD_FIXTURES,
        ]);
    }

    /**
     * Tidy up the test case.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->commandTester);
    }

    /**
     * Returns the project dir.
     *
     * @return string
     * @throws ConfigurationMissingException
     * @throws ArrayKeyNotFoundException
     */
    protected function getProjectDir(): string
    {
        if (!isset($this->parameterBag)) {
            throw new ConfigurationMissingException('$this->parameterBag is not set. Use $this->setConfigUseParameterBag().');
        }

        if (!$this->parameterBag->has(self::NAME_KERNEL_PROJECT_DIR)) {
            throw new ArrayKeyNotFoundException(self::NAME_KERNEL_PROJECT_DIR);
        }

        return strval($this->parameterBag->get(self::NAME_KERNEL_PROJECT_DIR));
    }
}
