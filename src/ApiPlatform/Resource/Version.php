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

namespace Ixnode\PhpApiVersionBundle\ApiPlatform\Resource;

use ApiPlatform\Metadata\Get;
use Ixnode\PhpApiVersionBundle\ApiPlatform\Route\VersionRoute;
use Ixnode\PhpApiVersionBundle\ApiPlatform\Resource\Base\BasePublicResource;
use Ixnode\PhpApiVersionBundle\ApiPlatform\State\VersionProvider;

/**
 * Class Version
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
#[Get(
    openapiContext: [
        'summary' => VersionRoute::SUMMARY_GET,
        'description' => VersionRoute::DESCRIPTION_GET,
        'responses' => [
            '200' => [
                'description' => 'Version resource',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => "#/components/schemas/Version"
                        ]
                    ]
                ]
            ]
        ]
    ],
    provider: VersionProvider::class
)]
class Version extends BasePublicResource
{
    private string $name;

    private string $description;

    private string $version;

    private string $date;

    private string $license;

    /** @var array<int, string> $authors */
    private array $authors;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return self
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return self
     */
    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicense(): string
    {
        return $this->license;
    }

    /**
     * @param string $license
     * @return self
     */
    public function setLicense(string $license): self
    {
        $this->license = $license;

        return $this;
    }

    /**
     * @return array<int, string>
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @param array<int, string> $authors
     * @return self
     */
    public function setAuthors(array $authors): self
    {
        $this->authors = $authors;

        return $this;
    }
}
