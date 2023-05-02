<!-- Charts -->
<div class="tnp-cards-container">
    <div class="tnp-card tnp-card-col-2">
        <div class="tnp-card-title">Opens</div>
        <div class="tnp-card-chart h-400">
            <canvas id="tnp-opens-chart"></canvas>
        </div>
    </div>
    <div class="tnp-card tnp-card-col-2">
        <div class="tnp-card-title">Clicks</div>
        <div class="tnp-card-chart h-400">
            <canvas id="tnp-clicks-chart"></canvas>
        </div>
    </div>
</div>
<div class="tnp-cards-container">
    <div class="tnp-card tnp-card-col-2">
        <div class="tnp-card-title">Reactivity: how many click after opening (%)</div>
        <div class="tnp-card-chart h-400">
            <canvas id="tnp-reactivity-chart"></canvas>
        </div>
    </div>
</div>
<script>
    jQuery(function ($) {
        new Chart('tnp-opens-chart', {
            type: 'line',
            data: {
                labels: <?php echo json_encode($overview_labels) ?>,
                datasets: [
                    {
                        label: "Opens",
                        fill: false,
                        strokeColor: "#2980b9",
                        backgroundColor: "#2980b9",
                        borderColor: "#2980b9",
                        pointBorderColor: "#2980b9",
                        pointBackgroundColor: "#2980b9",
                        data: <?php echo json_encode($overview_open_rate) ?>
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{
                            type: "category",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }],
                    yAxes: [
                        {
                            type: "linear",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }
                    ]
                },
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        afterTitle: function (data) {
                            return titles[data[0].index];
                        }
                    }
                },
                legend: {
                    labels: {
                        fontColor: "#ECF0F1"
                    }
                }
            }
        });

        new Chart('tnp-clicks-chart', {
            type: 'line',
            data: {
                labels: <?php echo json_encode($overview_labels) ?>,
                datasets: [
                    {
                        label: "Clicks",
                        fill: false,
                        strokeColor: "#2980b9",
                        backgroundColor: "#2980b9",
                        borderColor: "#2980b9",
                        pointBorderColor: "#2980b9",
                        pointBackgroundColor: "#2980b9",
                        data: <?php echo json_encode($overview_click_rate) ?>
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [
                        {
                            type: "category",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }
                    ],
                    yAxes: [
                        {
                            type: "linear",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }
                    ]
                },
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        afterTitle: function (data) {
                            return titles[data[0].index];
                        }
                    }
                },
                legend: {
                    labels: {
                        fontColor: "#ECF0F1"
                    }
                }
            }
        });

        new Chart('tnp-reactivity-chart', {
            type: 'line',
            data: {
                labels: <?php echo json_encode($overview_labels) ?>,
                datasets: [
                    {
                        label: "Reactivity",
                        fill: false,
                        strokeColor: "#2980b9",
                        backgroundColor: "#2980b9",
                        borderColor: "#2980b9",
                        pointBorderColor: "#2980b9",
                        pointBackgroundColor: "#2980b9",
                        data: <?php echo json_encode($overview_reactivity) ?>
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [
                        {
                            type: "category",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }
                    ],
                    yAxes: [
                        {
                            type: "linear",
                            gridLines: {display: true},
                            ticks: {fontColor: "#ECF0F1"}
                        }
                    ]
                },
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        afterTitle: function (data) {
                            return titles[data[0].index];
                        }
                    }
                },
                legend: {
                    labels: {
                        fontColor: "#ECF0F1"
                    }
                }
            }
        });

    });
</script>