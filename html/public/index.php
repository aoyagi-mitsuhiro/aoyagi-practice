<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Src\{universityRepo, DataScraper, DataDisplay};

$dsn = "mysql:host=aoyagi-db;dbname=aoyagi_mysql_db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, "root", "12345", $options);
    $db = new universityRepo($pdo);
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
