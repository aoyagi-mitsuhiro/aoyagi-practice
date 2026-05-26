<?php

namespace App\Src;

use DOMDocument;

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
