<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Functions;

final class SimpleDb
{
    private \PDO $pdo;

    public function __construct(string $connectionUri)
    {
        $db = self::parseDatabaseUri($connectionUri);

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => true,
        ];

        $this->pdo = new \PDO($db['dsn'], $db['user'], $db['password'], $options);
    }

    public static function createReadOnlyConnection(): self
    {
        return new SimpleDb($_ENV['READONLY_DATABASE_URL']);
    }

    // TODO: we need a way to support transactions before we can even start to think about allowing this:
    // TODO: we need to be careful when using persistent connections with transactions and cursors, as they can cause problems:
    /*
    public static function createReadWriteConnection()
    {
        return new SimpleDb($_ENV['DATABASE_URL']);
    }
    */

    public static function parseDatabaseUri(string $uri)
    {
        $parts = parse_url($uri);

        $driver = $parts['scheme'];
        $host = $parts['host'];
        $port = $parts['port'];
        $user = $parts['user'];
        $pass = $parts['pass'];
        $dbname = ltrim($parts['path'], '/');

        return [
            'dsn' => "$driver:host=$host" . ($port ? ";port=$port" : "") . ";dbname=$dbname",
            'user' => $user,
            'password' => $pass
        ];
    }

    public function query(string $query, ?array $parameters = null)
    {
        $statement = $this->pdo->prepare($query);

        if($parameters)
            $statement->execute($parameters);
        else
            $statement->execute();

        return new SimpleStatement($statement);
    }
}

final class SimpleStatement
{
    private \PDOStatement $statement;

    public function __construct(\PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    public function mapResults(string|callable $classNameOrMappingFunction): array
    {
        if(is_callable($classNameOrMappingFunction))
            return $this->statement->fetchAll(\PDO::FETCH_FUNC, $classNameOrMappingFunction);
        else
            return $this->statement->fetchAll(\PDO::FETCH_CLASS, $classNameOrMappingFunction);
    }

    public function getResults(): array
    {
        return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getSingleValue()
    {
        return $this->statement->fetchColumn();
    }
}
