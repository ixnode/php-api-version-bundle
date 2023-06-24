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

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\State\Base;

use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use Ixnode\PhpApiVersionBundle\ApiPlatform\Resource\Base\BasePublicResource;
use Ixnode\PhpApiVersionBundle\ApiPlatform\Route\VersionRoute;
use Ixnode\PhpApiVersionBundle\ApiPlatform\State\VersionProvider;
use Ixnode\PhpApiVersionBundle\Utils\TypeCasting\TypeCastingHelper;
use Ixnode\PhpException\ArrayType\ArrayKeyNotFoundException;
use Ixnode\PhpException\Case\CaseInvalidException;
use Ixnode\PhpException\Type\TypeInvalidException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class BaseProvider
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 * @implements ProviderInterface<BasePublicResource>
 */
abstract class BaseProvider implements ProviderInterface, ProcessorInterface
{
    protected InputInterface $input;

    /** @var array<string, InputArgument|InputOption> $inputArguments */
    protected array $inputArguments = [];

    /** @var array<string, mixed> $inputArgumentValues */
    protected array $inputArgumentValues = [];

    protected const NAME_KERNEL_PROJECT_DIR = 'kernel.project_dir';

    protected const TEXT_UNDEFINED_METHOD = 'Please overwrite the "%s" method in your provider to use this function.';

    /**
     * BaseProvider constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(protected ParameterBagInterface $parameterBag)
    {
        $this->input = new ArrayInput([]);
    }

    /**
     * Do the provided job and returns the base resource.
     *
     * @return BasePublicResource|BasePublicResource[]
     * @throws CaseInvalidException
     * @noRector
     */
    abstract protected function doProvide(): BasePublicResource|array;

    /**
     * Do the processed job and returns the resource wrapper.
     *
     * @return BasePublicResource
     * @throws CaseInvalidException
     * @noRector
     */
    abstract protected function doProcess(): BasePublicResource;

    /**
     * Binds given input definitions.
     *
     * @param InputArgument[]|InputOption[] $inputs
     * @return void
     */
    private function bindInputDefinition(array $inputs): void
    {
        $inputDefinition = new InputDefinition($inputs);

        $this->input->bind($inputDefinition);
    }

    /**
     * Sets given argument.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setArgument(string $name, mixed $value): void
    {
        $this->inputArguments[$name] = new InputArgument($name);

        $this->inputArgumentValues[$name] = $value;
    }

    /**
     * @return InputInterface
     */
    public function getInputInterface(): InputInterface
    {
        $this->bindInputDefinition(array_values($this->inputArguments));

        foreach ($this->inputArguments as $name => $input) {
            match (true) {
                $input instanceof InputArgument => $this->input->setArgument($name, $this->inputArgumentValues[$name]),
                $input instanceof InputOption => $this->input->setOption($name, $this->inputArgumentValues[$name]),
            };
        }

        return $this->input;
    }

    /**
     * Returns the route properties according to current class.
     *
     * @return array<string, array<string, int|string|string[]>>
     * @throws CaseInvalidException
     */
    protected function getRouteProperties(): array
    {
        return match (static::class) {
            VersionProvider::class => VersionRoute::PROPERTIES,
            default => throw new CaseInvalidException(static::class, [])
        };
    }

    /**
     * Gets the project directory.
     *
     * @return string
     * @throws ArrayKeyNotFoundException
     * @throws TypeInvalidException
     */
    protected function getProjectDir(): string
    {
        if (!$this->parameterBag->has(self::NAME_KERNEL_PROJECT_DIR)) {
            throw new ArrayKeyNotFoundException(self::NAME_KERNEL_PROJECT_DIR);
        }

        return (new TypeCastingHelper($this->parameterBag->get(self::NAME_KERNEL_PROJECT_DIR)))->strval();
    }
}
