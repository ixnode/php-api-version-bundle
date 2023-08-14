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

namespace Ixnode\PhpApiVersionBundle\Utils\Version;

use Doctrine\ORM\EntityManagerInterface;
use Ixnode\BashVersionManager\Version as BashVersionManager;
use Ixnode\PhpApiVersionBundle\Constants\Code\Environments;
use Ixnode\PhpException\Case\CaseUnsupportedException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class Version
 *
 * @author Björn Hempel <bjoern@hempel.li>
 * @version 0.1.0 (2023-06-25)
 * @since 0.1.0 (2023-06-25) First version.
 */
class Version extends BashVersionManager
{
    /**
     * Returns the symfony version of this application.
     *
     * @return string
     */
    public function getVersionSymfony(): string
    {
        return Kernel::VERSION;
    }

    /**
     * Returns the db driver name and version.
     *
     * @throws CaseUnsupportedException
     */
    public function getDriverName(EntityManagerInterface $entityManager): string
    {
        $connection = $entityManager->getConnection();

        $driver = $connection->getDriver()->getDatabasePlatform();

        $platformClassName = $driver::class;

        return match (true) {
            str_contains($platformClassName, 'MariaDBPlatform') => 'MariaDB - unknown version', /* @link MariaDBPlatform */
            str_contains($platformClassName, 'MariaDb1027Platform') => 'MariaDB - 10.x', /* @link MariaDb1027Platform */
            str_contains($platformClassName, 'MySQL57Platform') => 'MySQL - 5.7', /* @link MySQL57Platform */
            str_contains($platformClassName, 'MySQL80Platform') => 'MySQL - 8.0', /* @link MySQL80Platform */
            str_contains($platformClassName, 'MySQLPlatform') => 'MySQL - unknown version', /* @link MySQLPlatform */
            str_contains($platformClassName, 'PostgreSQL100Platform') => 'PostgreSQL 10.0', /* @link PostgreSQL100Platform */
            str_contains($platformClassName, 'PostgreSQL94Platform') => 'PostgreSQL 9.4', /* @link PostgreSQL94Platform */
            str_contains($platformClassName, 'PostgreSQLPlatform') => 'PostgreSQL - unknown version', /* @link PostgreSQLPlatform */
            str_contains($platformClassName, 'SqlitePlatform') => 'Sqlite - unknown version', /* @link SqlitePlatform */
            default => throw new CaseUnsupportedException(sprintf('Unsupported database platform "%s".', $platformClassName)),
        };
    }

    /**
     * Returns the environment name of this application.
     *
     * @param KernelInterface $kernel
     * @return string
     * @throws CaseUnsupportedException
     */
    public function getEnvironment(KernelInterface $kernel): string
    {
        return match($kernel->getEnvironment()) {
            Environments::DEVELOPMENT => Environments::DEVELOPMENT_NAME,
            Environments::STAGING => Environments::STAGING_NAME,
            Environments::PRODUCTION => Environments::PRODUCTION_NAME,
            Environments::TEST => Environments::TEST_NAME,
            default => throw new CaseUnsupportedException(sprintf('Unsupported environment "%s".', $kernel->getEnvironment())),
        };
    }
}
