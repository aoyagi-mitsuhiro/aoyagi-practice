<?php
echo "MySQL connect success! <br><br>";

$host = "192.168.2.94"; 
$user = "root";
$password = "12345"; 
$dbname = "mysql";
$japanese_university_table = "japanese_university";

$conn = new mysqli($host, $user, $password, $dbname);


if ($conn->connect_error) {
    die("MySQL connect fail: " . $conn->connect_error);
}

$url = "https://ja.wikipedia.org/wiki/%E6%97%A5%E6%9C%AC%E3%81%AE%E5%A4%A7%E5%AD%A6%E4%B8%80%E8%A6%A7_(%E4%BA%94%E5%8D%81%E9%9F%B3%E9%A0%86)";
$option = [
    'http' => [
        'method' => "GET",
        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)"
    ]
];

$context = stream_context_create($option);
$html_source = file_get_contents($url, false, $context);

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
            $safe_name = $conn->real_escape_string($name);
            $insert_query = "INSERT IGNORE INTO $japanese_university_table (university_name) VALUES ('$safe_name')";
            $conn->query($insert_query);
            
            echo $count . "番目の大学 : " . $safe_name . "<br>";
            $count++;
        }
    } 

} else {
    echo "Failed to parse HTML.";
}


$conn->close();
?>