<?php

declare(strict_types=1);

namespace App\Db;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use PDO;
use RuntimeException;
use SensitiveParameter;

/**
 * A DBAL driver that opens PDO from config['db'].
 *
 * Each server's config/autoload/global.php holds the block in this format:
 *
 *     'db' => [
 *         'driver' => 'Pdo',
 *         'dsn' => 'mysql:dbname=odkdash;host=localhost',
 *         'driver_options' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"],
 *         'username' => '...',
 *         'password' => '...',
 *     ],
 *
 * Without 'dsn', a driver such as 'Pdo_Mysql' builds one from 'database',
 * 'hostname', 'port', 'charset' and 'unix_socket'. Any other key is passed
 * to PDO as an option.
 */
final class ConfigPdoDriver extends AbstractMySQLDriver
{
    /** @param array<array-key, mixed> $config config['db'] */
    public function __construct(#[SensitiveParameter] private readonly array $config)
    {
    }

    /** @param array<string, mixed> $params DBAL's params, unused. The config holds the settings. */
    public function connect(#[SensitiveParameter] array $params): Connection
    {
        $pdo = new PDO(...self::pdoArguments($this->config));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return new Connection($pdo);
    }

    /**
     * The DSN, user name, password and options PDO opens with.
     *
     * @param array<array-key, mixed> $config
     * @return array{0: string, 1: ?string, 2: ?string, 3: array<array-key, mixed>}
     */
    public static function pdoArguments(#[SensitiveParameter] array $config): array
    {
        $dsn = $username = $password = $hostname = $database = $pdoDriver = null;
        $port = $charset = $unixSocket = $version = null;
        $options = [];
        foreach ($config as $key => $value) {
            switch (strtolower((string) $key)) {
                case 'dsn':
                    $dsn = (string) $value;
                    break;
                case 'driver':
                    $value = strtolower((string) $value);
                    if (str_starts_with($value, 'pdo')) {
                        $pdoDriver = substr(str_replace(['-', '_', ' '], '', $value), 3);
                    }
                    break;
                case 'pdodriver':
                    $pdoDriver = (string) $value;
                    break;
                case 'user':
                case 'username':
                    $username = (string) $value;
                    break;
                case 'pass':
                case 'password':
                    $password = (string) $value;
                    break;
                case 'host':
                case 'hostname':
                    $hostname = (string) $value;
                    break;
                case 'port':
                    $port = (int) $value;
                    break;
                case 'database':
                case 'dbname':
                    $database = (string) $value;
                    break;
                case 'charset':
                    $charset = (string) $value;
                    break;
                case 'unix_socket':
                    $unixSocket = (string) $value;
                    break;
                case 'version':
                    $version = (string) $value;
                    break;
                case 'driver_options':
                case 'options':
                    $value = (array) $value;
                    $options = array_diff_key($options, $value) + $value;
                    break;
                default:
                    $options[$key] = $value;
                    break;
            }
        }

        if ($hostname !== null && $unixSocket !== null) {
            throw new RuntimeException("config['db'] sets both hostname and unix_socket. Set one.");
        }
        if ($dsn === null && $pdoDriver !== null && $pdoDriver !== '') {
            $parts = [];
            foreach (['dbname' => $database, 'host' => $hostname, 'port' => $port, 'charset' => $charset, 'unix_socket' => $unixSocket, 'version' => $version] as $name => $part) {
                if ($part !== null) {
                    $parts[] = $name . '=' . $part;
                }
            }
            $dsn = $pdoDriver . ':' . implode(';', $parts);
        }
        if ($dsn === null) {
            throw new RuntimeException("config['db'] has no 'dsn', and its 'driver' cannot build one. See config/autoload/global.dist.php.");
        }

        return [$dsn, $username, $password, $options];
    }
}
