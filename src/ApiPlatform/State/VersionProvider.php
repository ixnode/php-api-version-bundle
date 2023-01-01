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

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\State;

use Ixnode\PhpApiVersionBundle\ApiPlatform\Resource\Base\BasePublicResource;
use Ixnode\PhpApiVersionBundle\ApiPlatform\Resource\Version as VersionResource;
use Ixnode\PhpApiVersionBundle\ApiPlatform\State\Base\Raw\BaseRawProvider;
use Ixnode\BashVersionManager\Version;
use Ixnode\PhpException\ArrayType\ArrayKeyNotFoundException;
use Ixnode\PhpException\File\FileNotFoundException;
use Ixnode\PhpException\Function\FunctionJsonEncodeException;
use Ixnode\PhpException\Type\TypeInvalidException;
use JsonException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

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
     */
    public function __construct(protected ParameterBagInterface $parameterBag)
    {
        $this->version = new Version();

        parent::__construct($parameterBag);
    }

    /**
     * Do the provided job.
     *
     * @return BasePublicResource
     * @throws FileNotFoundException
     * @throws ArrayKeyNotFoundException
     * @throws FunctionJsonEncodeException
     * @throws TypeInvalidException
     * @throws JsonException
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
}
