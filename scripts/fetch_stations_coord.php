<?php
set_time_limit(0);
$stationsPath = __DIR__ . "/../data/stations.json";
$stations = json_decode(file_get_contents($stationsPath), true);

function parseDMS(string $part): float {
    preg_match("/(\d+)([\d.]+)'([NSEW])/", $part, $m);
    $combined = $m[1];
    $decimal  = $m[2];

    $deg = (int)substr($combined, 0, -2);
    $min = (float)(substr($combined, -2) . $decimal);
    $dir = $m[3];

    $result = $deg + $min / 60;
    return ($dir === 'S' || $dir === 'W') ? -$result : $result;
}

foreach ($stations as &$station) {
    $id   = $station['id'];
    $name = $station['name'];
    echo "Fetching coordinates for $id ($name)...\n";

    $url = "https://www.data.jma.go.jp/stats/etrn/view/monthly_s3_en.php?block_no=$id&view=1";
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => "MyClimateProject/1.0",
        CURLOPT_TIMEOUT        => 300,
    ]);
    $html = curl_exec($ch);

    if (!$html) {
        echo "❌ Failed to fetch $id\n";
        continue;
    }

    if (!preg_match('/<caption[^>]*>(.*?)<\/caption>/is', $html, $cap)) {
        echo "❌ No caption found for $id\n";
        continue;
    }

    $caption = html_entity_decode($cap[1]);
    $caption = preg_replace('/<sup>.*?<\/sup>/i', '', $caption);
    $caption = strip_tags($caption);

    $parts = preg_split('/\s*(Lat|Lon)\s*/i', $caption, -1, PREG_SPLIT_NO_EMPTY);

    if (count($parts) < 3) {
        echo "❌ Could not parse coordinates for $id\n";
        continue;
    }

    $station['lat'] = parseDMS($parts[1]);
    $station['lon'] = parseDMS($parts[2]);


    // usleep(500000);
}
unset($station);
file_put_contents($stationsPath, json_encode($stations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
