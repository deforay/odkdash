<?php

declare(strict_types=1);

namespace App\Db;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Laminas\Db\Adapter\Adapter;
use WeakMap;

/**
 * The one DBAL connection for each laminas-db adapter.
 *
 * Models still build each other with `new SomeTable($adapter)`. A model
 * moved to DBAL keeps that constructor and asks this class for its
 * connection, so every model and the container's Connection share one
 * object, and DBAL counts nested transactions in one place.
 *
 * Remove this class with laminas-db. Models then take a Connection.
 */
final class Dbal
{
    /** @var WeakMap<Adapter, Connection>|null */
    private static ?WeakMap $connections = null;

    public static function connection(Adapter $adapter): Connection
    {
        self::$connections ??= new WeakMap();

        return self::$connections[$adapter] ??= self::open($adapter);
    }

    /** DBAL on the adapter's PDO (see SharedPdoDriver), with the query log when it is on. */
    private static function open(Adapter $adapter): Connection
    {
        $driver = new SharedPdoDriver($adapter);
        $log = QueryLog::fromEnvironment();
        if ($log !== null) {
            $driver = (new LoggingMiddleware($log))->wrap($driver);
        }

        return new Connection([], $driver);
    }
}
