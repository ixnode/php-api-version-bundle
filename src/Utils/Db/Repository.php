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

namespace Ixnode\PhpApiVersionBundle\Utils\Db;

use Doctrine\ORM\EntityManagerInterface;
use Ixnode\PhpApiVersionBundle\Entity\Version;
use Ixnode\PhpApiVersionBundle\Repository\VersionRepository;
use Ixnode\PhpException\Class\ClassInvalidException;

/**
 * Class Repository
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
class Repository
{
    /**
     * Repository constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    /**
     * Returns the Entity class from given repository class.
     *
     * @param class-string $repositoryClassName
     * @return class-string
     * @throws ClassInvalidException
     */
    protected function getEntityClass(string $repositoryClassName): string
    {
        return match (true) {
            $repositoryClassName === VersionRepository::class => Version::class,
            default => throw new ClassInvalidException($repositoryClassName, VersionRepository::class),
        };
    }

    /**
     * Returns the repository class from given entity class.
     *
     * @template T of object
     * @param class-string<T> $entityClassName
     * @return T
     * @throws ClassInvalidException
     */
    public function getRepository(string $entityClassName)
    {
        $em = $this->entityManager;

        $entityName = $this->getEntityClass($entityClassName);

        /** @var T $repository */
        $repository = $em->getRepository($entityName);

        if (!$repository instanceof $entityClassName) {
            throw new ClassInvalidException($repository::class, $entityClassName);
        }

        return $repository;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
