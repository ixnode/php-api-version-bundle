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

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\State;

use Ixnode\PhpApiVersionBundle\ApiPlatform\Resource\Base\BasePublicResource;
use Ixnode\PhpApiVersionBundle\ApiPlatform\Resource\Version as VersionResource;
use Ixnode\PhpApiVersionBundle\ApiPlatform\State\Base\Raw\BaseRawProvider;
use Ixnode\BashVersionManager\Version;
use Ixnode\PhpException\ArrayType\ArrayKeyNotFoundException;
use Ixnode\PhpException\Case\CaseInvalidException;
use Ixnode\PhpException\File\FileNotFoundException;
use Ixnode\PhpException\File\FileNotReadableException;
use Ixnode\PhpException\Function\FunctionJsonEncodeException;
use Ixnode\PhpException\Type\TypeInvalidException;
use JsonException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class VersionProvider
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
final class VersionProvider extends BaseRawProvider
{
    protected Version $version;

    /**
     * VersionProvider constructor.
     *
     * @param ParameterBagInterface $parameterBag
     * @param RequestStack $requestStack
     */
    public function __construct(protected ParameterBagInterface $parameterBag, protected RequestStack $requestStack)
    {
        $this->version = new Version();

        parent::__construct($parameterBag, $requestStack);
    }

    /**
     * Do the provided job and returns the base resource.
     *
     * @inheritdoc
     * @throws FileNotFoundException
     * @throws ArrayKeyNotFoundException
     * @throws FunctionJsonEncodeException
     * @throws TypeInvalidException
     * @throws JsonException
     * @throws FileNotReadableException
     * @throws CaseInvalidException
     */
    protected function doProvide(): BasePublicResource
    {
        return (new VersionResource())
            ->setName($this->version->getName())
            ->setDescription($this->version->getDescription())
            ->setAuthors($this->version->getAuthors())
            ->setLicense($this->version->getLicense())
            ->setVersion($this->version->getVersion())
            ->setDate($this->version->getDate());
    }

    /**
     * Do the processed job and returns the resource wrapper.
     *
     * @inheritdoc
     */
    protected function doProcess(): BasePublicResource
    {
        return new VersionResource();
    }
}
