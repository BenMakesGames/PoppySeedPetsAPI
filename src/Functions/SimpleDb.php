<?php

namespace App\Functions;

final class SimpleDb
{
    private \PDO $pdo;

    public function __construct(string $connectionUri)
    {
        $db = self::parseDatabaseUri($connectionUri);

        $this->pdo = new \PDO($db['dsn'], $db['user'], $db['password']);
    }

    public static function createReadOnlyConnection(): self
    {
        return new SimpleDb($_ENV['READONLY_DATABASE_URL']);
    }

    // TODO: we need a way to support transactions before we can even start to think about allowing this:
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
        $port = isset($parts['port']) ? $parts['port'] : null;
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

    /**
     * @param string|callable $classNameOrMappingFunction
     * @return array
     */
    public function mapResults($classNameOrMappingFunction): array
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

    public function getSingleValue(): array
    {
        return $this->statement->fetchColumn();
    }
}
