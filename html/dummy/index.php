<?php
class Database
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


class DataScraper
{
    private $url = "https://ja.wikipedia.org/wiki/%E6%97%A5%E6%9C%AC%E3%81%AE%E5%A4%A7%E5%AD%A6%E4%B8%80%E8%A6%A7_(%E4%BA%94%E5%8D%81%E9%9F%B3%E9%A0%86)";

    public function getUniversities(): array
    {
        $option = [
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
            ]
        ];

        $context = stream_context_create($option);
        $htmlSource =  file_get_contents($this->url, false, $context);
        $universities = [];

        if ($htmlSource !== false) {
            $doc = new DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $htmlSource);
            $links = $doc->getElementsByTagName('a');

            foreach ($links as $link) {
                $name = trim($link->textContent);

                if (preg_match('/.+(大学|短期大学)$/u', $name)) {
                    if ($name === '国立大学' || $name === '公立大学' || $name === '私立大学') {
                        continue;
                    }
                    $universities[] = $name;
                }
            }
        }
        return $universities;
    }
}

class DataDisplay
{
    public function displayUniversitiesHTML(array $universities): void
    {
        $count = 1;
        foreach ($universities as $university) {
            echo $count . "番目の大学 : " . $university . "<br>";
            $count++;
        }
    }

    public function displayUniversitiesCLI(array $universities): void
    {
        $count = 1;
        foreach ($universities as $university) {
            echo $count . "番目の大学 : " . $university . "\n";
            $count++;
        }
    }
}


$dsn = "mysql:host=aoyagi-db;dbname=aoyagi_mysql_db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, "root", "12345", $options);
    $db = new Database($pdo);
    $scraper = new DataScraper();
    $display = new DataDisplay();

    $universities = $scraper->getUniversities();
    $db->insertTable($universities);

    if (php_sapi_name() === 'cli') {
        $display->displayUniversitiesCLI($universities);
    } else {
        $display->displayUniversitiesHTML($universities);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
