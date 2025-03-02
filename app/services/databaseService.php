<?php

namespace App\Services;

use PDO;
use Psr\Log\LoggerInterface;

class DatabaseService {
    private ?PDO $pdo = null;

    public function __construct(
        private array $settings,
        private LoggerInterface $logger,
        private LoggerInterface $sqlLogger
    ) {}
    
    // lazy load connection 
    private function connect(): void {
        if($this->pdo === null){
            $this->pdo = new PDO(
                "sqlsrv:Server={$this->settings['host']};Database={$this->settings['name']}",
                null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
            $this->logger->info("Database connection initialized.");
        }
    }

    public function query(string $sql, array $params = []): array {
        $this->connect();
        $this->sqlLogger->debug($sql, ['params' => $params]);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool {
        $this->connect();
        $this->sqlLogger->debug($sql, ['params' => $params]);

        return $this->pdo->prepare($sql)->execute($params);
    }

    public function getPdo(): PDO {
        $this->connect();
        return $this->pdo;
    }
}
