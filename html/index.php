<?php

class Database {
    private $connection;
    private $table_name = "japanese_university";
    private $column_name = "university_name";

    // 自動実行されるコンストラクタ
    public function __construct() {
        $host = "aoyagi-db"; 
        $user = "root";
        $password = "12345"; 
        $dbname = "aoyagi_mysql_db";

        $conn = new mysqli($host, $user, $password, $dbname);

        if ($conn->connect_error) {
            die("MySQL connect fail: " . $conn->connect_error);
        }
        $this->connection = $conn;
        echo "MySQL connect success! <br><br>";
        
        $this->createTable();
    }

    private function createTable(){
        $table_sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            {$this->column_name} VARCHAR(255) NOT NULL UNIQUE
        )";
        $this->connection->query($table_sql);
    }

    public function insertTable($name) {
        $safe_name = $this->connection->real_escape_string($name);
        $insert_query = "INSERT IGNORE INTO {$this->table_name} ({$this->column_name}) VALUES ('$safe_name')";
        $this->connection->query($insert_query);
        return $safe_name;
    }

    public function closeConnection() {
        $this->connection->close();
    }
}


class UniversityScraper {
    private $url = "https://ja.wikipedia.org/wiki/%E6%97%A5%E6%9C%AC%E3%81%AE%E5%A4%A7%E5%AD%A6%E4%B8%80%E8%A6%A7_(%E4%BA%94%E5%8D%81%E9%9F%B3%E9%A0%86)";

    public function fetchHTML() {
        $option = [
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
            ]
        ];

        $context = stream_context_create($option);
        return file_get_contents($this->url, false, $context);
    }

    public function parseHTML($html_source) {
        $universities = [];
        if($html_source !== false) {
            $doc = new DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $html_source);
            $links = $doc->getElementsByTagName('a');
            $count = 1;

            foreach($links as $link) {
                $name = trim($link->textContent);

                if(preg_match('/.+(大学|短期大学)$/u', $name)){
                    if ($name === '国立大学' || $name === '公立大学' || $name === '私立大学') {
                        continue;
                    }
                    $universities[] = $name;
                }
            } 
        } 
        return $universities;
    }

    public function displayUniversities($universities, $db) {
        $count = 1;
        foreach($universities as $university) {
            $saved_name = $db->insertTable($university);
            echo $count . "番目の大学 : " . $saved_name . "<br>";
            $count++;
        }
    }
}

$db = new Database();
$scraper = new UniversityScraper();

$html = $scraper->fetchHTML();

$universities = $scraper->parseHTML($html);

if(!empty($universities)) {
    $scraper->displayUniversities($universities, $db);
}

$db->closeConnection();
?>