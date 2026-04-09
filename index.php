<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JMA temperature charts</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body>
    <main id="app">
        <label for="stations_list"></label>
        <select v-model="selectedStation" name="stations_list" id="stations_list">
            <option disabled value="">Select a station</option>
            <option v-for="station in stations" :key="station.id" :value="station.id">{{station.station_name}}</option>
        </select>
        <div>
            <canvas id="chart"></canvas>
        </div>
    </main>
<script type="module" src="main.js"></script>
</body>
</html>