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
use Ixnode\PhpException\Case\CaseUnsupportedException;
use Ixnode\PhpException\Type\TypeInvalidException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BaseProvider
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 * @implements ProviderInterface<BasePublicResource>
 * @implements ProcessorInterface<ProcessorInterface, ProcessorInterface>
 */
abstract class BaseProvider implements ProviderInterface, ProcessorInterface
{
    protected InputInterface $input;

    /** @var array<string, InputArgument|InputOption> $inputArguments */
    protected array $inputArguments = [];

    /** @var array<string, mixed> $inputArgumentValues */
    protected array $inputArgumentValues = [];

    protected ?string $error = null;

    final public const NAME_KERNEL_PROJECT_DIR = 'kernel.project_dir';

    protected const TEXT_UNDEFINED_METHOD = 'Please overwrite the "%s" method in your provider to use this function.';

    protected Request $request;

    /**
     * @param ParameterBagInterface $parameterBag
     * @param RequestStack $requestStack
     * @throws CaseUnsupportedException
     */
    public function __construct(protected ParameterBagInterface $parameterBag, RequestStack $requestStack)
    {
        $this->input = new ArrayInput([]);

        $request = $requestStack->getCurrentRequest();

        if (is_null($request)) {
            throw new CaseUnsupportedException('Can\'t get the CurrentRequest class(<code>$this->getRequest()->getCurrentRequest();</code>).');
        }

        $this->request = $request;
    }

    /**
     * Do the provided job and returns the base resource.
     *
     * @return BasePublicResource|BasePublicResource[]
     * @throws CaseUnsupportedException
     * @noRector
     */
    protected function doProvide(): BasePublicResource|array
    {
        throw new CaseUnsupportedException(sprintf(self::TEXT_UNDEFINED_METHOD, __METHOD__));
    }

    /**
     * Do the processed job and returns the resource wrapper.
     *
     * @return BasePublicResource
     * @throws CaseUnsupportedException
     * @noRector
     */
    protected function doProcess(): BasePublicResource
    {
        throw new CaseUnsupportedException(sprintf(self::TEXT_UNDEFINED_METHOD, __METHOD__));
    }

    /**
     * Binds given input definitions.
     *
     * @param InputArgument[]|InputOption[] $inputs
     * @return void
     */
    protected function bindInputDefinition(array $inputs): void
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
     * @throws TypeInvalidException
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

    /**
     * Returns the current request.
     *
     * @return Request
     */
    protected function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the header bag.
     *
     * @return HeaderBag
     */
    protected function getHeaderBag(): HeaderBag
    {
        $request = $this->getRequest();

        return $request->headers;
    }

    /**
     * Returns if the given name exists as a header request.
     *
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        $headerBag = $this->getHeaderBag();

        return $headerBag->has($name);
    }

    /**
     * Returns the header bag request.
     *
     * @param string $name
     * @return string|null
     * @throws ArrayKeyNotFoundException
     */
    public function getHeader(string $name): ?string
    {
        if (!$this->hasHeader($name)) {
            throw new ArrayKeyNotFoundException($name);
        }

        return $this->getHeaderBag()->get($name);
    }

    /**
     * Returns the given name from header (as string).
     *
     * @param string $name
     * @return string|null
     */
    public function getHeaderAsStringOrNull(string $name): ?string
    {
        if (!$this->hasHeader($name)) {
            return null;
        }

        return strval($this->getHeaderBag()->get($name));
    }

    /**
     * Returns the given name from header (as string).
     *
     * @param string $name
     * @return string
     * @throws CaseUnsupportedException
     */
    public function getHeaderAsString(string $name): string
    {
        if (!$this->hasHeader($name)) {
            throw new CaseUnsupportedException(sprintf('Header missing "%s"', $name));
        }

        return strval($this->getHeaderBag()->get($name));
    }

    /**
     * Returns the given name from header (as float).
     *
     * @param string $name
     * @return float|null
     */
    public function getHeaderAsFloatOrNull(string $name): ?float
    {
        if (!$this->hasHeader($name)) {
            return null;
        }

        return floatval($this->getHeaderBag()->get($name));
    }

    /**
     * Returns the given name from header (as float).
     *
     * @param string $name
     * @return float
     * @throws CaseUnsupportedException
     */
    public function getHeaderAsFloat(string $name): float
    {
        if (!$this->hasHeader($name)) {
            throw new CaseUnsupportedException(sprintf('Header missing "%s"', $name));
        }

        return floatval($this->getHeaderBag()->get($name));
    }

    /**
     * Returns the given name from header (as integer).
     *
     * @param string $name
     * @return int|null
     */
    public function getHeaderAsIntOrNull(string $name): ?int
    {
        if (!$this->hasHeader($name)) {
            return null;
        }

        return intval($this->getHeaderBag()->get($name));
    }

    /**
     * Returns the given name from header (as integer).
     *
     * @param string $name
     * @return int
     * @throws CaseUnsupportedException
     */
    public function getHeaderAsInt(string $name): int
    {
        if (!$this->hasHeader($name)) {
            throw new CaseUnsupportedException(sprintf('Header missing "%s"', $name));
        }

        return intval($this->getHeaderBag()->get($name));
    }

    /**
     * Returns the given name from header (as bool).
     *
     * @param string $name
     * @return bool
     * @throws CaseInvalidException
     * @throws TypeInvalidException
     */
    public function isHeaderAsBoolean(string $name): bool
    {
        if (!$this->hasHeader($name)) {
            return false;
        }

        $value = (new TypeCastingHelper($this->getHeaderBag()->get($name)))->strval();

        return match ($value) {
            'true' => true,
            'false' => false,
            default => throw new CaseInvalidException($value, ['true', 'false']),
        };
    }

    /**
     * Gets an error of this resource.
     *
     * @return string|null
     */
    protected function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Sets an error of this resource.
     *
     * @param string|null $error
     * @return self
     */
    protected function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }
}
