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

namespace Ixnode\PhpApiVersionBundle\Tests\Api\Base;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Exception;
use Ixnode\PhpException\Class\ClassInvalidException;
use Ixnode\PhpException\File\FileNotFoundException;
use Ixnode\PhpException\Function\FunctionJsonEncodeException;
use Ixnode\PhpException\Type\TypeInvalidException;
use Ixnode\PhpJsonSchemaValidator\Validator;
use Ixnode\PhpJsonSchemaValidator\ValidatorDebugger;
use JsonException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class BaseApiTestCase
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
abstract class BaseApiTestCase extends ApiTestCase
{
    protected ParameterBagInterface $parameterBag;

    protected RouterInterface $router;

    protected static bool $setUpDone = false;

    protected static Client $client;

    protected static ContainerInterface $container;

    protected const PARAMETER_NAME_API_BASE_URL = 'api.base_url';

    final public const MESSAGE_API_RESPONSE_INVALID = 'The returned api result does not match with the given schema.';

    final public const MESSAGE_API_RESPONSE_INVALID_WITH_ERROR = 'The returned api result does not match with the given schema:%s';

    /**
     * This method is called before class.
     *
     * @param string[] $kernelOptions  Options to pass to the createKernel method
     * @param string[] $defaultOptions Default options for the requests
     * @throws Exception
     */
    public static function initClientEnvironment(array $kernelOptions = [], array $defaultOptions = []): void
    {
        /* If setup is already done. Stop here. */
        if (self::$setUpDone) {
            return;
        }

        /* Create client. */
        self::$client = self::createClient($kernelOptions, $defaultOptions);

        /* Setup is already done. */
        self::$setUpDone = true;

        /* Save container class. */
        self::$container = self::$kernel->getContainer();
    }

    /**
     * Sets up the test case.
     *
     * @return void
     * @throws ClassInvalidException
     * @throws TypeInvalidException
     */
    protected function setUp(): void
    {
        $this->createService(ParameterBagInterface::class);
        $this->createService(RouterInterface::class);
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
            $service instanceof ParameterBagInterface => $this->parameterBag = $service,
            $service instanceof RouterInterface => $this->router = $service,
            default => throw new ClassInvalidException($service::class, [
                ParameterBagInterface::class,
                RouterInterface::class,
            ]),
        };
    }

    /**
     * Returns the route of given name and parameters.
     *
     * @param string $name
     * @param array<int|string, mixed> $parameter
     * @return string
     */
    protected function getRoute(string $name, array $parameter = []): string
    {
        return $this->router->generate($name, $parameter);
    }

    /**
     * Does an API request.
     *
     * @param string $name
     * @param array<int|string, mixed> $parameters
     * @param string $method
     * @param string|null $body
     * @param array<string, string>|null $headers
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     * @throws TypeInvalidException
     */
    protected function doRequest(string $name, array $parameters = [], string $method = Request::METHOD_GET, ?string $body = null, ?array $headers = null): ResponseInterface
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];

        /* Add body if method is a POST request */
        if ($method === Request::METHOD_POST) {
            if (is_null($body)) {
                throw new TypeInvalidException('string', 'null');
            }

            $options['body'] = $body;
        }

        if (!is_null($headers)) {
            $options['headers'] = [...$options['headers'], ...$headers];
        }

        return self::$client->request($method, $this->getRoute($name, $parameters), $options);
    }

    /**
     * Empty tearDown.
     * Will be done with self::tearDownAfterClass.
     */
    protected function tearDown(): void
    {
    }

    /**
     * Tear down after test.
     */
    public static function tearDownAfterClass(): void
    {
        static::ensureKernelShutdown();
        static::$booted = false;
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
     */
    protected function validateAndWriteOutput(Validator $validator): bool
    {
        return (new ValidatorDebugger($validator))->validate();
    }
}
