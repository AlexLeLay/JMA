const { createApp } = Vue;

createApp({
    data() {
        return {
            stations: [],
            months: {
                1: "January",
                2: "February",
                3: "March",
                4: "April",
                5: "May",
                6: "June",
                7: "July",
                8: "August",
                9: "September",
                10: "October",
                11: "November",
                12: "December"
            },
            selectedStation: "47402",
            chart: null
        };
    },
    mounted() {
        fetch("data/stations.json")
        .then(res => res.json())
        .then(data => {
            this.stations = data.map(element => ({
                station_name: element.name,
                id: element.id
            }));
            console.log(this.stations);
            
        });
        this.createChart();
    },
    methods: {
        fetchStationData(id) {
            fetch(`data/${id}.json`)
            .then(res => res.json())
            .then(json => {
                if (!json || !json.data) return;
                console.log(json);
                const labels = json.data.map(element => element.year);
                const temps = json.data.map(element => {
                    const march = element.months.find(m => m.month === 3)
                    return march ? march.temp : null;
                });
                const label = json.station

                if (!this.chart) {
                    this.createChart(labels, temps, label);

                } else {
                    this.chart.data.labels = labels;
                    this.chart.data.datasets[0].data = temps;
                    this.chart.data.datasets[0].label = json.station + ' - March °C';
                    updateChart();
                }
            })
        },
        createChart() {
            const canvas = document.getElementById('chart');
            canvas.width = 400;
            canvas.height = 300;
            const ctx = canvas.getContext("2d");

            ctx.beginPath();
            ctx.moveTo(30, 0);
            ctx.lineTo(30, 250);
            ctx.lineTo(380, 250);
            ctx.strokeStyle = "#000";
            ctx.stroke();
        },
        updateChart(labels, data, label) {

        }

    },
    watch: {
        selectedStation(newId) {
            if (newId) {
                console.log(newId);
                
                this.fetchStationData(newId);
            }
        }
    }
}).mount("#app");