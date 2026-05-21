<?php
$pdo = new PDO('mysql:host=aoyagi-db;dbname=aoyagi_mysql_db;charset=utf8', 'root', '12345');

$dataFether = new DataFethcer();
$dataFether->fetchFromUrl("https://ja.wikipedia.org/wiki/%E6%97%A5%E6%9C%AC%E3%81%AE%E5%A4%A7%E5%AD%A6%E4%B8%80%E8%A6%A7_(%E4%BA%94%E5%8D%81%E9%9F%B3%E9%A0%86)");
$data = $dataFether->getData();

$dataOputtter = new DataOutputter();
if (php_sapi_name() === 'cli') {
    $dataOputtter->outputCli($data);
} else {
    $dataOputtter->outputHtml($data);
}
$dataOputtter->savetoDatabase($data, $pdo);

class DataFethcer
{
    private array $data = [];

    public function fetchFromUrl(string $url)
    {
        $option = [
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
            ]
        ];

        $context = stream_context_create($option);
        $html_source = file_get_contents($url, false, $context);

        if ($html_source !== false) {
            $doc = new DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $html_source);
            $links = $doc->getElementsByTagName('a');

            foreach ($links as $link) {
                $name = trim($link->textContent);

                if (preg_match('/.+(大学|短期大学)$/u', $name)) {
                    if ($name === '国立大学' || $name === '公立大学' || $name === '私立大学') {
                        continue;
                    }
                    $this->data[] = $name;
                }
            }
        } else {
            echo "Failed to parse HTML.";
        }
    }
    public function getData(): array
    {
        return $this->data;
    }
}

class DataOutputter
{
    public function outputHtml(array $data)
    {
        foreach ($data as $index => $name) {
            echo ($index + 1) . "番目の大学 : " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "<br>";
        }
    }

    public function savetoDatabase(array $data, PDO $pdo)
    {
        $stmt = $pdo->prepare("INSERT IGNORE INTO japanese_university (university_name) VALUES (:name)");
        foreach ($data as $name) {
            $stmt->execute([':name' => $name]);
        }
    }

    public function outputCli(array $data)
    {
        foreach ($data as $index => $name) {
            echo ($index + 1) . "番目の大学 : " . $name . "\n";
        }
    }
}
