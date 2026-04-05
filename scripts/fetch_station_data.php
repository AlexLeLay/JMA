<?php

$stationsPath = __DIR__ . "/../data/stations.json";
$outputDir    = __DIR__ . "/../data/";

$stations = json_decode(file_get_contents($stationsPath), true);

if (!$stations) {
    die("Failed to load stations.json\n");
}

foreach ($stations as $station) {
    $id   = $station['id'];
    $name = $station['name'];

    echo "Processing $id ($name)...\n";

    $url = "https://www.data.jma.go.jp/stats/etrn/view/monthly_s3_en.php?block_no=$id&view=1";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => "MyClimateProject/1.0",
        CURLOPT_TIMEOUT        => 20,
    ]);

    $html = curl_exec($ch);

    if ($html === false) {
        echo "❌ Failed to fetch $id\n";
        continue;
    }


    libxml_use_internal_errors(true);

    $dom = new DOMDocument();
    $dom->loadHTML($html);

    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    /** @var DOMElement|null $table */
    $table = $xpath->query('//table[contains(@class, "data2_s")]')->item(0);

    if (!$table) {
        echo "❌ No data table for $id\n";
        continue;
    }

    $rows = $table->getElementsByTagName('tr');

    $data = [];

    foreach ($rows as $i => $row) {
        if ($i === 0) continue;

        $cols = $row->getElementsByTagName('td');

        if ($cols->length < 13) continue;

        $yearText = trim($cols->item(0)->textContent);
        if (!is_numeric($yearText)) continue;

        $year = (int)$yearText;

        $yearData = [
            "year" => $year,
            "months" => []
        ];

        for ($m = 1; $m <= 12; $m++) {
            $raw = trim($cols->item($m)->textContent);

            if ($raw === '' || $raw === '---' || $raw === '///') {
                $temp = null;
            } elseif (is_numeric($raw)) {
                $temp = (float)$raw;
            } else {
                $temp = null;
            }

            $yearData["months"][] = [
                "month" => $m,
                "temp"  => $temp
            ];
        }

        $data[] = $yearData;
    }

    if (empty($data)) {
        echo "⚠️ No usable data for $id\n";
        continue;
    }

    $output = [
        "id"      => $id,
        "station" => $name,
        "data"    => $data
    ];

    $filePath = $outputDir . $id . ".json";

    file_put_contents(
        $filePath,
        json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    // sleep(1);
}