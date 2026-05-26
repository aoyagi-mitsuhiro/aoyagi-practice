<?php

namespace App\Src;

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
