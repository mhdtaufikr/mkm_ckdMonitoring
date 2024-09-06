@extends('layouts.master')

@section('content')
<style>
    #lblGreetings {
        font-size: 1rem; /* Adjust the base font size as needed */
    }

    @media only screen and (max-width: 600px) {
        #lblGreetings {
            font-size: 1rem; /* Adjust the font size for smaller screens */
        }
    }
    .page-header .page-header-content {
        padding-top: 0rem;
        padding-bottom: 1rem;
    }
    .chart-container {
    margin-top: 0px;
    position: relative;
    height: 75%; /* Adjust the height as needed */
    width: 100%; /* Use auto for dynamic width */
}
.chart-custom {
    width: 100% !important;
    height: 100% !important; /* Let the canvas take the full height of the container */
}

.card-custom {
    height: 100%; /* Adjust the height as needed */
    width: 100%; /* Adjust the width as needed */
}


    .indicator-table {
        width: 100%;
        margin-bottom: 10px;
        text-align: center;
        border-collapse: collapse;
    }
    .indicator-table th, .indicator-table td {
        border: 1px solid black;
        padding: 5px;
    }
    .signal {
        display: inline-block;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        line-height: 40px;
        text-align: center;
        color: white;
        font-weight: bold;
    }
    .green {
        background-color: green;
    }
    .yellow {
        background-color: yellow;
        color: black;
    }
    .red {
        background-color: red;
    }
</style>

<main>
    <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
        <div class="container-fluid px-4">
            <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between">
                    <!-- Add date range picker input -->
                    <div class="col-auto">
                        <form action="{{ url('/home/ckd') }}" method="POST" enctype="multipart/form-data">
                            @csrf <!-- Include the CSRF token for security -->
                            <div class="d-flex justify-content-end align-items-end">
                                <div class="d-flex align-items-end">
                                    <!-- Use type="month" for month selection only, and form-control-sm for small input -->
                                    <input type="month" id="monthPicker" name="selected_month" class="form-control form-control-sm me-2" placeholder="Select month">
                                    <button type="submit" class="btn btn-sm btn-primary">Submit</button> <!-- Submit button -->
                                </div>
                            </div>

                        </form>


                    </div>

                </div>
            </div>
        </div>
    </header>

    <section class="content">
        <div class="container-fluid px-4 mt-n10">
            <div class="row">
                <!-- Variant Code Summary Chart -->
                <div class="col-md-5 mb-2">
                    <div  class="card card-custom">
                        <div class="card-header">
                            <h4>Stock Per Variant at MKM</h4>
                        </div>
                        <div class="card-body">
                            <div  id="variant-code-pie-chart" style="margin-top: 0px;
    position: relative;
    height: 100%;
    width: 100%;"></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-7 mb-2">
                    <!-- OTDC Chart Carousel -->
                    <div style="height: 526px" class="card card-custom mb-2">
                        <div class="card-header">
                            <h4>OTDC Senopati to MKM</h4>
                        </div>
                        <div class="card-body">
                            @if($vendorData->isNotEmpty())
                                @foreach ($vendorData as $vendorName => $data)
                                    @php
                                        $totalPercentage = 0;
                                        $count = 0;
                                        $today = now()->format('Y-m-d');
                                        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
                                        $includedEntries = [];
                                        $uniqueDates = [];

                                        foreach ($data as $entry) {
                                            if ($entry->date >= $startOfMonth && $entry->date <= $today) {
                                                if (!in_array($entry->date, $uniqueDates)) {
                                                    if (!isset($entry->total_planned_qty) || $entry->total_actual_qty > $entry->total_planned_qty) {
                                                        $entry->percentage = 100;
                                                    }
                                                    $totalPercentage += $entry->percentage;
                                                    $count++;
                                                    $includedEntries[] = $entry;
                                                    $uniqueDates[] = $entry->date;
                                                }
                                            }
                                        }

                                        $averagePercentage = ($count > 0) ? $totalPercentage / $count : 0;
                                    @endphp

                                    <div class="row">
                                        <div class="col-md-8">
                                            <table class="indicator-table mb-4">
                                                <tr>
                                                    <th>Signal Indicator</th>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <span class="signal green px-2">G</span> ≥ 95%
                                                        <span class="signal yellow">Y</span> ≥ 85%
                                                        <span class="signal red">R</span> < 85%
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4">
                                            <table class="indicator-table mb-4">
                                                <tr>
                                                    <th>Average OTDC</th>
                                                    <th>Signal</th>
                                                </tr>
                                                <tr>
                                                    <td>{{ number_format($averagePercentage, 2) }}%</td>
                                                    <td>
                                                        <span id="signal-otdc" class="signal
                                                            {{ $averagePercentage >= 95 ? 'green' : ($averagePercentage >= 85 ? 'yellow' : 'red') }}">
                                                            {{ $averagePercentage >= 95 ? 'G' : ($averagePercentage >= 85 ? 'Y' : 'R') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <p style="margin-top: -20px" class="text-center">{{ $vendorName }}</p>
                                    <div style="margin-top: -20px; width: 100%; height: 80%; margin-left: 0px;" class="chart-container">
                                        <div class="chart-custom" id="chartdiv"></div>
                                    </div>
                                @endforeach
                            @else
                                <p>No data available for this period.</p>
                            @endif
                        </div>

                    </div>

                    <script>
                        am5.ready(function() {
                            var root = am5.Root.new("chartdiv");

                            root.setThemes([am5themes_Animated.new(root)]);

                            var chart = root.container.children.push(
                                am5xy.XYChart.new(root, {
                                    panX: false,
                                    panY: false,
                                    wheelX: "none",
                                    wheelY: "none",
                                    paddingLeft: 0,
                                    layout: root.verticalLayout
                                })
                            );

                            const vendorData = @json($vendorData);
                            const data = vendorData.SENOPATI.map(item => ({
                                date: new Date(item.date).getDate().toString(),
                                actual: parseInt(item.total_actual_qty),
                                plan: parseInt(item.total_planned_qty),
                                percentage: parseFloat(item.percentage)
                            }));

                            // Predefine x-axis categories to 1-31
                            var daysOfMonth = Array.from({ length: 31 }, (_, i) => (i + 1).toString());

                            var xRenderer = am5xy.AxisRendererX.new(root, {
                                minorGridEnabled: true,
                                minGridDistance: 30
                            });

                            var xAxis = chart.xAxes.push(
                                am5xy.CategoryAxis.new(root, {
                                    categoryField: "date",
                                    renderer: xRenderer,
                                    tooltip: am5.Tooltip.new(root, {})
                                })
                            );

                            xAxis.data.setAll(daysOfMonth.map(date => ({ date }))); // Set x-axis to 1-31
                            xRenderer.grid.template.setAll({ location: 1 });

                            var yAxis = chart.yAxes.push(
                                am5xy.ValueAxis.new(root, {
                                    min: 0,
                                    extraMax: 0.1,
                                    renderer: am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 })
                                })
                            );

                            yAxis.children.moveValue(am5.Label.new(root, {
                                rotation: -90,
                                text: "Quantity",
                                y: am5.p50,
                                centerX: am5.p50
                            }), 0);

                            var yAxisRight = chart.yAxes.push(
                                am5xy.ValueAxis.new(root, {
                                    min: 0,
                                    max: 120, // Set the max value to 120%
                                    renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
                                })
                            );

                            yAxisRight.children.moveValue(am5.Label.new(root, {
                                rotation: -90,
                                text: "Percentage (%)",
                                y: am5.p50,
                                centerX: am5.p50
                            }), 0);

                            var planSeries = chart.series.push(
                                am5xy.ColumnSeries.new(root, {
                                    name: "Plan",
                                    xAxis: xAxis,
                                    yAxis: yAxis,
                                    valueYField: "plan",
                                    categoryXField: "date",
                                    clustered: true,
                                    tooltip: am5.Tooltip.new(root, {
                                        pointerOrientation: "horizontal",
                                        labelText: "{name}: {valueY}"
                                    })
                                })
                            );
                            planSeries.columns.template.setAll({ fill: am5.color("#1e81b0"), width: am5.percent(80), tooltipY: am5.percent(10), marginLeft: 0 });
                            planSeries.data.setAll(data);

                            var actualSeries = chart.series.push(
                                am5xy.ColumnSeries.new(root, {
                                    name: "Actual",
                                    xAxis: xAxis,
                                    yAxis: yAxis,
                                    valueYField: "actual",
                                    categoryXField: "date",
                                    clustered: true,
                                    tooltip: am5.Tooltip.new(root, {
                                        pointerOrientation: "horizontal",
                                        labelText: "{name}: {valueY}"
                                    })
                                })
                            );
                            actualSeries.columns.template.setAll({ fill: am5.color("#fbb659"), width: am5.percent(80), tooltipY: am5.percent(10), marginRight: 0 });
                            actualSeries.data.setAll(data);

                            var percentageSeries = chart.series.push(
                                am5xy.LineSeries.new(root, {
                                    name: "Percentage",
                                    xAxis: xAxis,
                                    yAxis: yAxisRight,
                                    valueYField: "percentage",
                                    categoryXField: "date",
                                    tooltip: am5.Tooltip.new(root, {
                                        pointerOrientation: "horizontal",
                                        labelText: "{name}: {valueY}%"
                                    }),
                                    stroke: am5.color(0x000000),
                                    fill: am5.color(0x000000)
                                })
                            );
                            percentageSeries.strokes.template.setAll({ strokeWidth: 3 });
                            percentageSeries.data.setAll(data);
                            percentageSeries.bullets.push(function(root, series, dataItem) {
                                var value = dataItem.dataContext.percentage;
                                var bulletColor = value < 100 ? am5.color(0xff0000) : am5.color(0x00ff00);
                                return am5.Bullet.new(root, {
                                    sprite: am5.Circle.new(root, {
                                        strokeWidth: 3,
                                        stroke: series.get("stroke"),
                                        radius: 5,
                                        fill: bulletColor
                                    })
                                });
                            });

                            // Add click event to actualSeries columns
                            actualSeries.columns.template.events.on("click", function(ev) {
                                var date = ev.target.dataItem.dataContext.date;
                                window.open('/details-page/' + date, '_blank');
                            });

                            // Add click event to planSeries columns
                            planSeries.columns.template.events.on("click", function(ev) {
                                var date = ev.target.dataItem.dataContext.date;
                                window.open('/details-page/' + date, '_blank');
                            });

                            // Function to create trend line
                            function createTrendLine(data, color) {
                                var series = chart.series.push(
                                    am5xy.LineSeries.new(root, {
                                        name: "Trend Line",
                                        xAxis: xAxis,
                                        yAxis: yAxisRight,
                                        valueXField: "date",
                                        stroke: color,
                                        strokeWidth: 4,
                                        valueYField: "value"
                                    })
                                );

                                series.data.setAll(data);
                                series.strokes.template.setAll({ stroke: color, strokeWidth: 4, strokeDasharray: [5, 5] });
                                series.appear(1000, 100);
                            }

                            var trendLineData = data.map((item) => ({ date: item.date, value: item.percentage }));
                            createTrendLine(trendLineData, root.interfaceColors.get("positive"));

                            chart.set("cursor", am5xy.XYCursor.new(root, {
                                behavior: "none"
                            }));

                            var legend = chart.children.push(
                                am5.Legend.new(root, {
                                    centerX: am5.p50,
                                    x: am5.p50
                                })
                            );
                            legend.data.setAll(chart.series.values);

                            chart.appear(1000, 100);
                            actualSeries.appear();
                            planSeries.appear();
                            percentageSeries.appear();
                        });
                    </script>






                  <!-- Item Code Quantity Carousel -->
                  <div style="height: 375px" class="card card-custom">
                    <div class="card-header">
                        <h4>Stock of Hand All CKD at MKM</h4>
                    </div>
                    <div class="card-body">
                        <div id="itemCodeQuantityCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div hidden class="carousel-indicators">
                                @foreach ($itemCodeQuantities as $groupIndex => $group)
                                    <button type="button" data-bs-target="#itemCodeQuantityCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                @endforeach
                            </div>
                            <div style="margin-top: -20px" class="carousel-inner">
                                @foreach ($itemCodeQuantities as $groupIndex => $group)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <p class="text-center">Group {{ $groupIndex + 1 }}</p>
                                        <div style="height: 275px; width: 100% " class="chart-container">
                                            <canvas  id="item-code-quantity-chart-{{ $groupIndex }}" class="chart-custom"></canvas>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#itemCodeQuantityCarousel" data-bs-slide="prev">
                                <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#itemCodeQuantityCarousel" data-bs-slide="next">
                                <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>




                </div>

                <div class="col-md-5 mb-4">
                    <!-- Second Variant Code Summary Chart -->

                        <div class="card card-custom">
                            <div class="card-header">
                                <h4>Stock Accumulation CNI / 1 Agustus 2024</h4>
                            </div>
                            <div class="card-body">
                                <div id="variant-code-pie-chart-cni" style="margin-top: 0px; height: 250px; width: 100%;"></div>
                            </div>
                        </div>



                        <script>
                            am5.ready(function() {

                                // Create root element
                                var root = am5.Root.new("variant-code-pie-chart-cni");

                                // Set themes
                                root.setThemes([
                                    am5themes_Animated.new(root)
                                ]);

                                // Create chart
                                var chart = root.container.children.push(am5percent.PieChart.new(root, {
                                    radius: am5.percent(90),
                                    innerRadius: am5.percent(50),
                                    layout: root.horizontalLayout
                                }));

                                // Create series
                                var series = chart.series.push(am5percent.PieSeries.new(root, {
                                    name: "Series",
                                    valueField: "total_qty",  // Adjusted for your data
                                    categoryField: "name"    // Adjusted for your data
                                }));

                                // Set data (using your dynamic data from Laravel)
                                const variantCodeQuantitiesCNI = @json($variantCodeQuantitiesCNI);
                                series.data.setAll(variantCodeQuantitiesCNI);

                                // Disabling ticks
                                series.ticks.template.set("visible", false);

                                // Showing labels with name and quantity
                                series.labels.template.setAll({
                                    text: "{category}: {value}",  // Display name and quantity on the chart
                                    visible: true,                // Ensure labels are visible
                                    radius: 20,                   // Position the labels
                                    inside: false,                // Place the labels outside the slices
                                    fill: am5.color(0x000000)     // Set label color to black
                                });

                                // Adding gradients
                                series.slices.template.set("strokeOpacity", 0);
                                series.slices.template.set("fillGradient", am5.RadialGradient.new(root, {
                                    stops: [{
                                        brighten: -0.8
                                    }, {
                                        brighten: -0.8
                                    }, {
                                        brighten: -0.5
                                    }, {
                                        brighten: 0
                                    }, {
                                        brighten: -0.5
                                    }]
                                }));

                                // Create legend
                                var legend = chart.children.push(am5.Legend.new(root, {
                                    centerY: am5.percent(50),
                                    y: am5.percent(50),
                                    layout: root.verticalLayout
                                }));

                                // Set value labels align to right
                                legend.valueLabels.template.setAll({ textAlign: "right" });

                                // Set width and max width of labels
                                legend.labels.template.setAll({
                                    maxWidth: 140,
                                    width: 140,
                                    oversizedBehavior: "wrap"
                                });

                                legend.data.setAll(series.dataItems);

                                // Play initial series animation
                                series.appear(1000, 100);

                            }); // end am5.ready()
                        </script>

                </div>

              <!-- Inventory Monitoring Carousel -->
<div class="col-md-7 mb-2">
    <div class="card card-custom">
        <div class="card-header">
            <h4>OTDC Supply to CNI</h4>
        </div>
        <div class="card-body">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
    <div hidden class="carousel-indicators">
        @foreach ($plannedData as $itemName => $comparisons)
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
        @endforeach
    </div>
    <div class="carousel-inner">
        @if($plannedData->isNotEmpty())
            @foreach ($plannedData as $itemName => $comparisons)
                @php
                    // Find the current item in the result data
                    $currentItem = collect($resultData)->firstWhere('item_name', $itemName);
                    $averagePercentage = $currentItem ? $currentItem['average_percentage'] : 0;
                @endphp

                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                    <div class="row">
                        <div class="col-md-8">
                            <table class="indicator-table mb-4">
                                <tr>
                                    <th>Signal Indicator</th>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="signal green px-2">G</span> ≥ 95%
                                        <span class="signal yellow">Y</span> ≥ 85%
                                        <span class="signal red">R</span> < 85%
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="indicator-table mb-4">
                                <tr>
                                    <th>Average Supply</th>
                                    <th>Signal</th>
                                </tr>
                                <tr>
                                    <td>{{ number_format($averagePercentage, 2) }}%</td>
                                    <td>
                                        <span id="signal-inventory" class="signal
                                            {{ $averagePercentage >= 95 ? 'green' : ($averagePercentage >= 85 ? 'yellow' : 'red') }}">
                                            {{ $averagePercentage >= 95 ? 'G' : ($averagePercentage >= 85 ? 'Y' : 'R') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <p style="margin-top: -20px" class="text-center">{{ $itemName }}</p>
                    <div style="margin-top: -20px; height: 215px; width: 100%;" class="chart-container">
                        <canvas id="chart-{{ str_replace(' ', '_', $itemName) }}" class="chart-custom"></canvas>
                    </div>
                </div>
            @endforeach
        @else
            <div class="carousel-item active">
                <p class="text-center">No data available for this period.</p>
            </div>
        @endif
    </div>


    <button class="carousel-control-prev btn-sm" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
        <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span hidden class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next btn-sm" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
        <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
        <span hidden class="visually-hidden">Next</span>
    </button>

    <style>
        .carousel-control-prev,
        .carousel-control-next {
            width: 80px; /* Adjust the width */
            height: 80px; /* Adjust the height */
            padding: 0px; /* Adjust padding if needed */
        }
    </style>

</div>

        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const groupedPlannedData = @json($plannedData);
        const groupedActualData = @json($actualData);
        const today = new Date().getDate();

        // Function to create Inventory Monitoring chart
        function createInventoryMonitoringChart(itemName) {
            const plannedComparisons = groupedPlannedData[itemName];
            const actualComparisons = groupedActualData[itemName] || [];

            const plannedData = Array(31).fill(null);
            const actualData = Array(31).fill(null);
            const percentageDifference = Array(31).fill(0);

            plannedComparisons.forEach((comparison) => {
                const plannedDay = new Date(comparison.planned_receiving_date).getDate() - 1;
                plannedData[plannedDay] = comparison.planned_qty;
            });

            actualComparisons.forEach((comparison) => {
                const actualDay = new Date(comparison.receiving_date).getDate() - 1;
                actualData[actualDay] = comparison.received_qty;
            });

            plannedData.forEach((value, index) => {
                if (value !== null && actualData[index] !== null) {
                    const percentage = Math.min((actualData[index] / value) * 100, 100);
                    percentageDifference[index] = isFinite(percentage) ? percentage : 0;
                }
            });

            // Create the Chart.js instance
            const ctx = document.getElementById(`chart-${itemName.replace(/\s+/g, '_')}`).getContext('2d');
            new Chart(ctx, {
                type: 'bar', // Bar for planned and actual, line for percentage
                data: {
                    labels: Array.from({ length: 31 }, (_, i) => (i + 1).toString()), // Days of the month
                    datasets: [
                        {
                            label: 'Planned Supply',
                            data: plannedData,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            type: 'bar',
                            yAxisID: 'y',
                        },
                        {
                            label: 'Actual Supply',
                            data: actualData,
                            backgroundColor: 'rgba(255, 159, 64, 0.8)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1,
                            type: 'bar',
                            yAxisID: 'y',
                        },
                        {
                            label: 'Percentage Accuracy',
                            data: percentageDifference,
                            borderColor: 'rgba(0, 0, 0, 1)',
                            borderWidth: 2,
                            fill: false,
                            type: 'line',
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Quantity'
                            },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Percentage (%)'
                            },
                            beginAtZero: true,
                            max: 120
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.dataset.type === 'line') {
                                        return `${context.dataset.label}: ${context.raw}%`;
                                    }
                                    return `${context.dataset.label}: ${context.raw}`;
                                }
                            }
                        }
                    },
                    onClick: function(e, activeElements) {
                        if (activeElements.length > 0) {
                            const datasetIndex = activeElements[0].datasetIndex;
                            const index = activeElements[0].index;
                            const selectedDate = this.data.labels[index];

                            if (datasetIndex !== undefined && index !== undefined) {
                                const fullDate = `${new Date().getFullYear()}-${String(new Date().getMonth() + 1).padStart(2, '0')}-${String(selectedDate).padStart(2, '0')}`;
                                window.open(`/details-page/cni/${fullDate}`, '_blank');
                            }
                        }
                    }
                }
            });
        }

        // Loop through each itemName to create charts
        Object.keys(groupedPlannedData).forEach(itemName => {
            createInventoryMonitoringChart(itemName);
        });
    });
</script>


                <div class="col-12">
                    <div class="card">
                        <div class="card-header" data-bs-toggle="collapse" href="#collapseCard" role="button" aria-expanded="false" aria-controls="collapseCard">
                            <h3 class="card-title">List of Item Balance Item</h3>
                        </div>

                        <!-- /.card-header -->
                        <div id="collapseCard" class="collapse">
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-sm-12">
                                    </div>
                                    <div class="table-responsive">
                                        <table id="tableUser" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Item Code</th>
                                                    <th>Vendor Name</th>
                                                    <th>Planned Receiving Date</th>
                                                    <th>Planned Quantity</th>
                                                    <th>Received Quantity</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $no = 1;
                                                @endphp
                                                @foreach ($itemNotArrived as $notArrived)
                                                    <tr>
                                                        <td>{{ $no++ }}</td>
                                                        <td>{{ $notArrived->item_code }}</td>
                                                        <td>{{ $notArrived->vendor_name }}</td>
                                                        <td>{{ date('d M Y', strtotime($notArrived->planned_receiving_date)) }}</td>
                                                        <td>{{ $notArrived->planned_qty }}</td>
                                                        <td>{{ $notArrived->received_qty }}</td>
                                                        <td>{{ $notArrived->balance }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {


      const variantCodeQuantities = @json($variantCodeQuantities);

      if (typeof variantCodeQuantities === 'object') {
        const combinedData = [];

        Object.keys(variantCodeQuantities).forEach((groupIndex) => {
          const group = variantCodeQuantities[groupIndex];
          group.forEach(item => {
            if (item.total_qty > 0) { // Only include items with a quantity greater than 0
              combinedData.push({ category: item.model, value: item.total_qty });
            }
          });
        });

        // Create the combined chart using the provided template structure
        am5.ready(function() {
            // Create root element
            var root = am5.Root.new("variant-code-pie-chart");

            // Set themes
            root.setThemes([
                am5themes_Animated.new(root)
            ]);

            // Create chart
            var chart = root.container.children.push(am5percent.PieChart.new(root, {
                radius: am5.percent(70),
                innerRadius: am5.percent(50),
                layout: root.horizontalLayout
            }));

            // Create series
            var series = chart.series.push(am5percent.PieSeries.new(root, {
                name: "Series",
                valueField: "value",
                categoryField: "category"
            }));

            // Set data
            series.data.setAll(combinedData);

            // Disabling labels and ticks
            series.labels.template.set("visible", true); // Show labels
            series.labels.template.set("text", "{category}: {value}"); // Display model and quantity
            series.ticks.template.set("visible", false);

            // Adding gradients
            series.slices.template.set("strokeOpacity", 0);
            series.slices.template.set("fillGradient", am5.RadialGradient.new(root, {
                stops: [{
                    brighten: -0.8
                }, {
                    brighten: -0.8
                }, {
                    brighten: -0.5
                }, {
                    brighten: 0
                }, {
                    brighten: -0.5
                }]
            }));

            // Configure tooltips to show model and quantity
            series.slices.template.set("tooltipText", "{category}: {value}");

            // Create legend
            var legend = chart.children.push(am5.Legend.new(root, {
                centerY: am5.percent(50),
                y: am5.percent(50),
                layout: root.verticalLayout
            }));

            // Set value labels align to right
            legend.valueLabels.template.setAll({ textAlign: "right" });

            // Set width and max width of labels
            legend.labels.template.setAll({
                maxWidth: 100,
                width: 100,
                oversizedBehavior: "wrap"
            });

            legend.data.setAll(series.dataItems);

            // Play initial series animation
            series.appear(1000, 100);
        }); // end am5.ready()
      } else {
        console.error('variantCodeQuantities is not an object:', variantCodeQuantities);
      }
    });
    </script>



<script>
    document.addEventListener('DOMContentLoaded', (event) => {


        const itemCodeQuantities = @json($itemCodeQuantities);
        const month = new Date().toLocaleString('default', { month: 'long' });

        if (typeof itemCodeQuantities === 'object') {
            Object.keys(itemCodeQuantities).forEach((groupIndex) => {


                const group = itemCodeQuantities[groupIndex];

                const itemCodes = group.map(item => item.code);
                const quantities = group.map(item => item.qty);
                const itemIds = group.map(item => item._id); // Add this line to get item IDs


                const ctx = document.getElementById(`item-code-quantity-chart-${groupIndex}`).getContext('2d');
                const myChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: itemCodes,
                        datasets: [{
                            label: 'Quantity',
                            data: quantities,
                            backgroundColor: 'rgba(54, 162, 235, 0.8)', // Increase opacity
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: false,
                                categoryPercentage: 0.5,
                                barPercentage: 0.5,
                                ticks: {
                                    autoSkip: false,
                                    maxRotation: 0,
                                    minRotation: 0
                                }
                            },
                            y: {
                                stacked: false,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Quantity'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    title: function(tooltipItems) {
                                        let title = tooltipItems[0].label || '';
                                        title += ` ${month}`;
                                        return title;
                                    },
                                    label: function(context) {
                                        return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) : '';
                                    }
                                }
                            }
                        },
                        onClick: (e, elements) => {

                            if (elements.length > 0) {
                                const chart = elements[0].chart;
                                const index = elements[0].index;
                                const itemId = itemIds[index]; // Get the item ID for the clicked bar
                                const url = `/inventory/${itemId}/details`;
                                window.open(url, '_blank');

                            }
                        }
                    }
                });
            });
        } else {
            console.error('itemCodeQuantities is not an object:', itemCodeQuantities);
        }
    });
</script>


<script>
    $(document).ready(function() {
      var table = $("#tableUser").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
      });
    });
</script>

<script>
    function refreshPage() {
        setTimeout(function() {
            location.reload();
        }, 200000); // 300000 milliseconds = 5 minutes
    }

    // Call the function when the page loads
    refreshPage();
</script>

@endsection
