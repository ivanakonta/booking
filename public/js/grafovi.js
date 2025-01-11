document.addEventListener('DOMContentLoaded', function () {
    var id = JSON.parse(document.getElementById('id').value);
    var vrstaDrvetaSelect = document.getElementById('vrstaDrvetaSelect');
    var periodSelect = document.getElementById('periodSelect');

    var initialVrstaDrvetaId = vrstaDrvetaSelect.value;
    var initialPeriod = periodSelect.value;

    // Fetch initial data
    if (initialVrstaDrvetaId) {
        fetchChartData(id, initialVrstaDrvetaId, initialPeriod);
    }

    // Fetch data when vrstaDrveta changes
    vrstaDrvetaSelect.addEventListener('change', function () {
        var vrstaDrvetaId = this.value;
        var period = periodSelect.value;
        if (vrstaDrvetaId) {
            fetchChartData(id, vrstaDrvetaId, period);
        }
    });

    // Fetch data when period changes
    periodSelect.addEventListener('change', function () {
        var period = this.value;
        var vrstaDrvetaId = vrstaDrvetaSelect.value;
        if (vrstaDrvetaId) {
            fetchChartData(id, vrstaDrvetaId, period);
        }
    });
});

var chart; // Ensure chart variable is accessible globally

function renderChart(data) {
    // Log data values for debugging
    console.log("Dovoz:", data.dovozOblovine);
    console.log("Prorez:", data.prorez);
    console.log("Rezana:", data.rezanaGrada);
    console.log("Iskorištenost:", data.iskoristenost);

    // Calculate maximum values for each series
    var maxDovoz = Math.max(...data.dovozOblovine || [0]);
    var maxProrez = Math.max(...data.prorez || [0]);
    var maxRezana = Math.max(...data.rezanaGrada || [0]);

    // Convert iskorištenost strings to numbers and find the maximum
    var iskorištenostNumerical = data.iskoristenost.map(Number);
    var maxIskorištenost = Math.max(...iskorištenostNumerical || [0]);

    // Get the overall maximum for left Y-axis (Dovoz, Prorez, Rezana)
    var maxLeft = Math.max(maxDovoz, maxProrez, maxRezana) * 1.1; // Add a bit of space

    // Set the right Y-axis max to a constant value for percentage
    var maxRight = 100;

    var options3 = {
        series: [{
            name: 'Dovoz oblovine (m³)',
            type: 'column',
            data: data.dovozOblovine || [],
            yAxisIndex: 0 // First left axis for Dovoz
        }, {
            name: 'Prorez oblovine (m³)',
            type: 'column',
            data: data.prorez || [],
            yAxisIndex: 1 // Second left axis for Prorez
        }, {
            name: 'Rezana građa (m³)',
            type: 'column',
            data: data.rezanaGrada || [],
            yAxisIndex: 2 // Third left axis for Rezana
        }, {
            name: 'Iskorištenost (%)',
            type: 'line',
            data: iskorištenostNumerical || [],
            yAxisIndex: 3 // Separate right axis for Iskorištenost
        }],
        chart: {
            type: 'line',
            height: 350,
            stacked: false
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                endingShape: 'rounded'
            },
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            width: [1, 1, 1, 4] // Different line width for Iskorištenost
        },
        xaxis: {
            categories: data.categories || [] // Categories are dynamically set
        },
        yaxis: [{
            title: {
                text: 'Oblovine (m³)', // Single title for all left axes
                style: {
                    color: '#00E396' // You can adjust the color
                }
            },
            labels: {
                formatter: function (value) {
                    return value.toFixed(2) + " m³";  // Ensures 2 decimal places
                }
            },
            min: 0,
            max: maxLeft, // Set max value for left axis
            forceNiceScale: true,
        }, {
            // This y-axis is effectively disabled visually (no title)
            labels: {
                show: false // Hides labels for the second left axis
            },
            min: 0,
            max: maxLeft, // Set max value for Prorez axis
            forceNiceScale: true,
        }, {
            // This y-axis is also effectively disabled visually (no title)
            labels: {
                show: false // Hides labels for the third left axis
            },
            min: 0,
            max: maxLeft, // Set max value for Rezana axis
            forceNiceScale: true,
        }, {
            opposite: true,
            title: {
                text: 'Iskorištenost (%)',
                style: {
                    color: '#f84d64'
                }
            },
            labels: {
                formatter: function (value) {
                    return value.toFixed(2) + "%";  // Percentage for Iskorištenost
                }
            },
            min: 0,
            max: maxRight,  // Set max value for right axis as 100
            forceNiceScale: true
        }],
        fill: {
            opacity: 1
        },
        tooltip: {
            y: {
                formatter: function (val, { seriesIndex }) {
                    return seriesIndex === 3
                        ? val.toFixed(2) + "%" // Percentage for Iskorištenost
                        : val.toFixed(2) + " m³"; // Cubic meters for others
                }
            }
        }
    };

    // If chart already exists, destroy it before creating a new one
    if (chart) {
        chart.destroy();
    }

    chart = new ApexCharts(document.querySelector("#chart-3"), options3);
    chart.render();
}

function fetchChartData(id, vrstaDrvetaId, period) {
    let url = period === 'monthly'
        ? `/dashboard/${id}/monthlydata/${vrstaDrvetaId}`
        : `/dashboard/${id}/weeklydata/${vrstaDrvetaId}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            console.log('Fetched data:', data);

            // Set categories dynamically based on period
            const categories = period === 'monthly'
                ? ['Siječanj', 'Veljača', 'Ožujak', 'Travanj', 'Svibanj', 'Lipanj', 'Srpanj', 'Kolovoz', 'Rujan', 'Listopad', 'Studeni', 'Prosinac']
                : ['Ponedjeljak', 'Utorak', 'Srijeda', 'Četvrtak', 'Petak', 'Subota', 'Nedjelja'];

            // Update data object to include categories for the chart
            data.debug.categories = categories;

            renderChart(data.debug);
        })
        .catch(error => console.error('Error fetching data:', error));
}
