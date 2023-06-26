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

namespace Ixnode\PhpApiVersionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ixnode\PhpApiVersionBundle\Entity\Trait\TimestampsTrait;
use Ixnode\PhpApiVersionBundle\Repository\VersionRepository;
use Stringable;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity class Version
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
#[ORM\Entity(repositoryClass: VersionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Version implements EntityInterface, Stringable
{
    use TimestampsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['version', 'version_extended'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['version', 'version_extended'])]
    private string $version;

    /**
     * __toString method.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getVersion();
    }

    /**
     * Gets the id of this version.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Gets the name of this version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Sets the name of this version.
     *
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }
}
