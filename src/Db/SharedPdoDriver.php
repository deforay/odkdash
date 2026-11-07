<?php

declare(strict_types=1);

namespace App\Db;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use Laminas\Db\Adapter\Adapter;
use SensitiveParameter;

/**
 * A DBAL driver that runs on the PDO connection the Laminas adapter opens.
 *
 * Code moving from laminas-db to DBAL keeps sharing one connection with the
 * code that has not moved yet. A transaction a service begins on the
 * adapter therefore covers the DBAL queries inside it.
 *
 * The adapter opens the connection from config['db'] as before, so the
 * per-server settings in config/autoload keep working unchanged.
 *
 * Remove this driver with laminas-db, once nothing uses the adapter.
 */
final class SharedPdoDriver extends AbstractMySQLDriver
{
    public function __construct(private readonly Adapter $adapter)
    {
    }

    /** @param array<string, mixed> $params DBAL's params, unused. The adapter holds the settings. */
    public function connect(#[SensitiveParameter] array $params): Connection
    {
        // getResource() connects if the adapter has not connected yet.
        return new Connection($this->adapter->getDriver()->getConnection()->getResource());
    }
}
