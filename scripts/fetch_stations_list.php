<?php
$data_dir = __DIR__ . "/../data";
$stations_file = "$data_dir/stations.json";

if (!file_exists($data_dir)) {
    mkdir($data_dir, 0777, true);
}

$url = "https://www.data.jma.go.jp/stats/etrn/view/monthly_s3_en.php?block_no=47401&view=1";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "MyClimateProject/1.0");
$html = curl_exec($ch);


preg_match_all('/<option value="(\d+)"[^>]*>([^&]+)&nbsp;&nbsp;WMO Station ID:/i', $html, $matches);

$stations = [];
for ($i = 0; $i < count($matches[1]); $i++) {
    $stations[] = [
        "id" => $matches[1][$i],
        "name" => trim($matches[2][$i])
    ];
}

file_put_contents($stations_file, json_encode($stations, JSON_PRETTY_PRINT));

?>