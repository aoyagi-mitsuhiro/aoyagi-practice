<?php

namespace App\Src;

use PDO;

class universityRepo
{
    private $tableName = "japanese_university";
    private $columnName = "university_name";
    private PDO $pdo;

    // 自動実行されるコンストラクタ
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->createTable();
    }

    private function createTable(): void
    {
        $tableSql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            {$this->columnName} VARCHAR(255) NOT NULL UNIQUE
        )";
        $this->pdo->query($tableSql);
    }

    public function insertTable(array $names): void
    {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO {$this->tableName} ({$this->columnName}) VALUES (:name)");
        foreach ($names as $name) {
            $stmt->execute(['name' => $name]);
        }
    }
}
