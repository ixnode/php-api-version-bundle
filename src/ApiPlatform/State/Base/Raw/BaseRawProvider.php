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

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\State\Base\Raw;

use ApiPlatform\Metadata\Operation;
use Ixnode\PhpApiVersionBundle\ApiPlatform\Resource\Base\BasePublicResource;
use Ixnode\PhpApiVersionBundle\ApiPlatform\State\Base\BaseProvider;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class BaseRawProvider
 *
 * Use this Provider to provide the raw data of the given BasePublicResource (doProvide) without any API specific wrapper information:
 *
 * - data resource
 * - given resource
 * - valid state of request
 * - date of request
 * - time-taken for request
 * - version of API
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
abstract class BaseRawProvider extends BaseProvider
{
    /**
     * BaseDirectProvider constructor.
     */
    public function __construct(protected ParameterBagInterface $parameterBag, protected RequestStack $request)
    {
        parent::__construct($this->parameterBag, $this->request);
    }

    /**
     * @param Operation $operation
     * @param array<string, mixed> $uriVariables
     * @param array<int|string, mixed> $context
     * @return BasePublicResource|BasePublicResource[]
     * @throws CaseUnsupportedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): BasePublicResource|array
    {
        return $this->doProvide();
    }

    /**
     * @param mixed $data
     * @param Operation $operation
     * @param array<string, mixed> $uriVariables
     * @param array<int|string, mixed> $context
     * @return BasePublicResource
     * @throws CaseUnsupportedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): BasePublicResource
    {
        return $this->doProcess();
    }
}
