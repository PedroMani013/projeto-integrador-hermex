<?php

declare(strict_types=1);

namespace Config;

use MongoDB\Client;

/**
 * Singleton para conexão com MongoDB.
 * Garante que apenas uma instância do Client seja criada por ciclo de request.
 */
class DatabaseConnection
{
    private static ?self $instance = null;
    private \MongoDB\Database $db;

    private function __construct()
    {
        $host   = $_ENV['MONGO_HOST']   ?? getenv('MONGO_HOST') ?: 'localhost';
        $port   = $_ENV['MONGO_PORT']   ?? getenv('MONGO_PORT') ?: '27017';
        $dbName = $_ENV['MONGO_DB']     ?? getenv('MONGO_DB')   ?: 'hermex';
        $user   = $_ENV['MONGO_USER']   ?? getenv('MONGO_USER') ?: '';
        $pass   = $_ENV['MONGO_PASS']   ?? getenv('MONGO_PASS') ?: '';

        if ($user !== '' && $pass !== '') {
            $uri = "mongodb://{$user}:{$pass}@{$host}:{$port}/{$dbName}?authSource=admin";
        } else {
            $uri = "mongodb://{$host}:{$port}";
        }

        $client   = new Client($uri);
        $this->db = $client->selectDatabase($dbName);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDb(): \MongoDB\Database
    {
        return $this->db;
    }

    /** Impede clonagem do Singleton */
    private function __clone() {}
}
