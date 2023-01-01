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
use Ixnode\PhpException\Db\DbEntityNotFoundException;

/**
 * Class Entity
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-01-01)
 * @since 0.1.0 (2023-01-01) First version.
 */
class Entity
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param class-string $className
     * @return class-string
     * @throws ClassInvalidException
     */
    protected function getRepositoryClass(string $className): string
    {
        return match (true) {
            $className === Version::class => VersionRepository::class,
            default => throw new ClassInvalidException($className, Version::class),
        };
    }

    /**
     * @template T of object
     * @param array<string, mixed> $criteria
     * @param class-string<T> $className
     * @return T|null
     * @throws ClassInvalidException
     */
    public function getOneNull(array $criteria, string $className)
    {
        $em = $this->entityManager;

        $repository = $em->getRepository($className);

        $repositoryName = $this->getRepositoryClass($className);

        if (!$repository instanceof $repositoryName) {
            throw new ClassInvalidException($repository::class, $repositoryName);
        }

        $entity = $repository->findOneBy($criteria);

        if ($entity === null) {
            return null;
        }

        if (!$entity instanceof $className) {
            throw new ClassInvalidException($entity::class, $className);
        }

        return $entity;
    }

    /**
     * @template T of object
     * @param array<string, mixed> $criteria
     * @param class-string<T> $className
     * @return T
     * @throws ClassInvalidException
     * @throws DbEntityNotFoundException
     */
    public function getOne(array $criteria, string $className)
    {
        $entity = $this->getOneNull($criteria, $className);

        if ($entity === null) {
            throw new DbEntityNotFoundException($className);
        }

        return $entity;
    }

    /**
     * Saves the given entity.
     *
     * @template T of object
     * @param T $entity
     * @return void
     */
    public function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}
