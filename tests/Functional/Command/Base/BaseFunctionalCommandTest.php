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

namespace Ixnode\PhpApiVersionBundle\Tests\Functional\Command\Base;

use Closure;
use Exception;
use Ixnode\PhpApiVersionBundle\Constants\Command\CommandData;
use Ixnode\PhpApiVersionBundle\Utils\Command\CommandHelper;
use Ixnode\PhpApiVersionBundle\Utils\Db\Entity;
use Ixnode\PhpApiVersionBundle\Utils\Db\Repository;
use Ixnode\PhpApiVersionBundle\Utils\TypeCasting\TypeCastingHelper;
use Ixnode\PhpChecker\Checker;
use Ixnode\PhpContainer\File;
use Ixnode\PhpException\ArrayType\ArrayKeyNotFoundException;
use Ixnode\PhpException\Class\ClassInvalidException;
use Ixnode\PhpException\Configuration\ConfigurationMissingException;
use Ixnode\PhpException\File\FileNotFoundException;
use Ixnode\PhpException\Function\FunctionJsonEncodeException;
use Ixnode\PhpException\Type\TypeInvalidException;
use Ixnode\PhpJsonSchemaValidator\Validator;
use Ixnode\PhpJsonSchemaValidator\ValidatorDebugger;
use JsonException;
use ReflectionClass;
use ReflectionException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
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

    /** @var class-string<ParameterBagInterface> $parameterBagClass */
    protected string $parameterBagClass = ParameterBagInterface::class;


    protected Entity $entity;

    /** @var class-string<Entity> $entityClass */
    protected string $entityClass = Entity::class;


    protected Repository $repository;

    /** @var class-string<Repository> $repositoryClass */
    protected string $repositoryClass = Repository::class;


    protected Environment $twig;

    /** @var class-string<Environment> $twigClass */
    protected string $twigClass = Environment::class;


    protected RequestStack $request;

    /** @var class-string<RequestStack> $requestClass */
    protected string $requestClass = RequestStack::class;


    protected TranslatorInterface $translator;

    /** @var class-string<TranslatorInterface> $translatorClass */
    protected string $translatorClass = TranslatorInterface::class;


    protected CommandHelper $commandHelper;

    /** @var class-string<CommandHelper> $commandHelperClass */
    protected string $commandHelperClass = CommandHelper::class;


    protected bool $useKernel = false;

    protected bool $useCommand = false;

    protected bool $useDb = false;

    protected bool $useParameterBag = false;

    protected bool $useTwig = false;

    protected bool $useRequestStack = false;

    protected bool $useRepository = false;

    protected bool $useTranslator = false;

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
     * @return self
     */
    protected function setConfigUseRequestStack(): self
    {
        $this->useRequestStack = true;

        return $this;
    }

    /**
     * @return self
     */
    protected function setConfigUseRepository(): self
    {
        $this->useRepository = true;

        return $this;
    }

    /**
     * @return self
     */
    protected function setConfigUseTranslator(): self
    {
        $this->useTranslator = true;

        return $this;
    }

    /**
     * Sets up the test case.
     *
     * @return void
     * @throws Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setUp(): void
    {
        $this->doConfig();

        if ($this->useKernel) {
            self::bootKernel();
        }

        if ($this->useParameterBag) {
            $this->createService($this->getServiceParameterBagClass());
        }

        if ($this->useDb) {
            $this->createService($this->getServiceEntityClass());
            $this->createService($this->getServiceRepositoryClass());
        }

        if ($this->useTwig) {
            $this->createService($this->getServiceEnvironmentClass());
        }

        if ($this->useRequestStack) {
            $this->createService($this->getServiceRequestStackClass());
        }

        if ($this->useTranslator) {
            $this->createService($this->getServiceTranslatorClass());
        }

        if ($this->loadFixtures) {
            $this->createService($this->getServiceCommandHelperClass());
            $this->loadFixtures();
        }

        if ($this->useCommand) {
            $this->createCommand($this->commandName, $this->commandClass, $this->commandClassParameterClosure);
        }
    }

    /**
     * Creates a service from given class string.
     *
     * @param class-string $serviceName
     * @return void
     * @throws ClassInvalidException
     * @throws Exception
     */
    protected function createService(string $serviceName): void
    {
        $container = self::getContainer();

        $service = $container->get($serviceName);

        match (true) {
            $service instanceof CommandHelper => $this->commandHelper = $service,
            $service instanceof Entity => $this->entity = $service,
            $service instanceof Environment => $this->twig = $service,
            $service instanceof ParameterBagInterface => $this->parameterBag = $service,
            $service instanceof Repository => $this->repository = $service,
            $service instanceof RequestStack => $this->request = $service,
            $service instanceof TranslatorInterface => $this->translator = $service,
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
     * @return class-string<ParameterBagInterface>
     */
    public function getServiceParameterBagClass(): string
    {
        return $this->parameterBagClass;
    }

    /**
     * @param class-string<ParameterBagInterface> $parameterBagClass
     * @return self
     */
    public function setServiceParameterBagClass(string $parameterBagClass): self
    {
        $this->parameterBagClass = $parameterBagClass;

        return $this;
    }

    /**
     * @return class-string<Entity>
     */
    public function getServiceEntityClass(): string
    {
        return $this->entityClass;
    }

    /**
     * @param class-string<Entity> $entityClass
     * @return self
     */
    public function setServiceEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * @return class-string<Repository>
     */
    public function getServiceRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * @param class-string<Repository> $repositoryClass
     * @return self
     */
    public function setServiceRepositoryClass(string $repositoryClass): self
    {
        $this->repositoryClass = $repositoryClass;

        return $this;
    }

    /**
     * @return class-string<Environment>
     */
    public function getServiceEnvironmentClass(): string
    {
        return $this->twigClass;
    }

    /**
     * @param class-string<Environment> $twigClass
     * @return self
     */
    public function setServiceEnvironmentClass(string $twigClass): self
    {
        $this->twigClass = $twigClass;

        return $this;
    }

    /**
     * @return class-string<RequestStack>
     */
    public function getServiceRequestStackClass(): string
    {
        return $this->requestClass;
    }

    /**
     * @param class-string<RequestStack> $requestClass
     * @return self
     */
    public function setServiceRequestClass(string $requestClass): self
    {
        $this->requestClass = $requestClass;

        return $this;
    }

    /**
     * @return class-string<TranslatorInterface>
     */
    public function getServiceTranslatorClass(): string
    {
        return $this->translatorClass;
    }

    /**
     * @param class-string<TranslatorInterface> $translatorClass
     * @return self
     */
    public function setServiceTranslatorClass(string $translatorClass): self
    {
        $this->translatorClass = $translatorClass;

        return $this;
    }

    /**
     * @return class-string<CommandHelper>
     */
    public function getServiceCommandHelperClass(): string
    {
        return $this->commandHelperClass;
    }

    /**
     * @param class-string<CommandHelper> $commandHelperClass
     * @return self
     */
    public function setServiceCommandHelperClass(string $commandHelperClass): self
    {
        $this->commandHelperClass = $commandHelperClass;
        return $this;
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
            $commandClassParameter = (new Checker($commandClassParameterClosure->call($this)))->checkArray();
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
     * @throws TypeInvalidException
     */
    protected function getProjectDir(): string
    {
        if (!isset($this->parameterBag)) {
            throw new ConfigurationMissingException('$this->parameterBag is not set. Use $this->setConfigUseParameterBag().');
        }

        if (!$this->parameterBag->has(self::NAME_KERNEL_PROJECT_DIR)) {
            throw new ArrayKeyNotFoundException(self::NAME_KERNEL_PROJECT_DIR);
        }

        return (new TypeCastingHelper($this->parameterBag->get(self::NAME_KERNEL_PROJECT_DIR)))->strval();
    }

    /**
     * Validates the given data and writes debug outputs.
     *
     * @param Validator $validator
     * @return bool
     * @throws TypeInvalidException
     * @throws FunctionJsonEncodeException
     * @throws JsonException
     * @throws FileNotFoundException
     * @throws JsonException
     */
    protected function validateAndWriteOutput(Validator $validator): bool
    {
        return (new ValidatorDebugger($validator))->validate();
    }
}
