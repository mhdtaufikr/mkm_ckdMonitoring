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
                    <div class="col-auto">
                        <h1 class="page-header-title"></h1>
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
                            <h4>Variant Code Summary</h4>
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
                            <h4>OTDC</h4>
                        </div>
                        <div class="card-body">
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
                            @endforeach

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
                            <div style="margin-top: -20px; width: 100%%; height: 100%%; margin-left: 0px;" class="chart-container">
                                <div  class="chart-custom" id="chartdiv"></div>
                            </div>
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
                        <h4>Item Code Quantities</h4>
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
                                <h4>Variant Code Summary CNI</h4>
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
                                    categoryField: "model"    // Adjusted for your data
                                }));

                                // Set data (using your dynamic data from Laravel)
                                const variantCodeQuantitiesCNI = @json($variantCodeQuantitiesCNI[1]);
                                series.data.setAll(variantCodeQuantitiesCNI);

                                // Disabling ticks
                                series.ticks.template.set("visible", false);

                                // Showing labels with model and quantity
                                series.labels.template.setAll({
                                    text: "{category}: {value}",  // Display model and quantity on the chart
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
                <div class="col-md-7 mb-2">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>OTDC CNI Data</h4>
                        </div>
                        <div class="card-body">
                            <div id="otdcCniCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div hidden class="carousel-indicators">
                                    @foreach ($otcdCniData as $model => $data)
                                        <button type="button" data-bs-target="#otdcCniCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach ($otcdCniData as $model => $data)
                                        @php
                                            $totalPercentage = 0;
                                            $count = 0;
                                            $today = now()->format('Y-m-d');
                                            foreach ($data as $entry) {
                                                if ($entry->date <= $today) {
                                                    $totalPercentage += $entry->average_percentage;
                                                    $count++;
                                                }
                                            }
                                            $averagePercentage = ($count > 0) ? $totalPercentage / $count : 0;
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
                                            <p style="margin-top: -20px" class="text-center">{{ $model }}</p>
                                            <div style="margin-top: -20px" class="chart-container">
                                                <div id="otdc-chart-{{ $model }}" class="chart-custom"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#otdcCniCarousel" data-bs-slide="prev">
                                    <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span hidden class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#otdcCniCarousel" data-bs-slide="next">
                                    <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span hidden class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    am5.ready(function() {
                        function createOTDCChart(model, plannedData, actualData, percentageAccuracy, endDate) {
                            var root = am5.Root.new(`otdc-chart-${model}`);

                            root.setThemes([am5themes_Animated.new(root)]);

                            var chart = root.container.children.push(am5xy.XYChart.new(root, {
                                panX: false,
                                panY: false,
                                wheelX: "none",
                                wheelY: "none",
                                layout: root.verticalLayout
                            }));

                            var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                                categoryField: "date",
                                tooltip: am5.Tooltip.new(root, {}),
                                renderer: am5xy.AxisRendererX.new(root, { minGridDistance: 30 })
                            }));

                            xAxis.data.setAll(Array.from({ length: endDate }, (_, i) => ({ date: (i + 1).toString() })));

                            var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                                min: 0,
                                renderer: am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 })
                            }));

                            var yAxisRight = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                                min: 0,
                                max: 120,
                                strictMinMax: true,
                                renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
                            }));

                            yAxis.children.moveValue(am5.Label.new(root, {
                                rotation: -90,
                                text: "Quantity",
                                y: am5.p50,
                                centerX: am5.p50
                            }), 0);

                            yAxisRight.children.moveValue(am5.Label.new(root, {
                                rotation: -90,
                                text: "Percentage (%)",
                                y: am5.p50,
                                centerX: am5.p50
                            }), 0);

                            var planSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                                name: "Planned Qty",
                                xAxis: xAxis,
                                yAxis: yAxis,
                                valueYField: "plan",
                                categoryXField: "date",
                                clustered: true,
                                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
                            }));

                            planSeries.columns.template.setAll({ fill: am5.color("#36A2EB"), width: am5.percent(80) });
                            planSeries.data.setAll(plannedData.slice(0, endDate).map((value, i) => ({ date: (i + 1).toString(), plan: value || 0 })));

                            var actualSeries = chart.series.push(am5xy.ColumnSeries.new(root, {
                                name: "Actual Qty",
                                xAxis: xAxis,
                                yAxis: yAxis,
                                valueYField: "actual",
                                categoryXField: "date",
                                clustered: true,
                                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
                            }));

                            actualSeries.columns.template.setAll({ fill: am5.color("#FF9F40"), width: am5.percent(80) });
                            actualSeries.data.setAll(actualData.slice(0, endDate).map((value, i) => ({ date: (i + 1).toString(), actual: value || 0 })));

                            var percentageSeries = chart.series.push(am5xy.LineSeries.new(root, {
                                name: "Percentage Accuracy",
                                xAxis: xAxis,
                                yAxis: yAxisRight,
                                valueYField: "percentage",
                                categoryXField: "date",
                                tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}%" }),
                                stroke: am5.color(0x000000),
                                fill: am5.color(0x000000)
                            }));

                            percentageSeries.strokes.template.setAll({ strokeWidth: 3 });
                            percentageSeries.data.setAll(percentageAccuracy.slice(0, endDate).map((value, i) => ({ date: (i + 1).toString(), percentage: value || 0 })));

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

                            var legend = chart.children.push(am5.Legend.new(root, {
                                centerX: am5.p50,
                                x: am5.p50
                            }));

                            legend.data.setAll(chart.series.values);

                            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                                behavior: "none",
                                xAxis: xAxis
                            }));

                            cursor.lineY.set("visible", false);

                            chart.appear(1000, 100);
                            actualSeries.appear();
                            planSeries.appear();
                            percentageSeries.appear();
                        }

                        const otcdCniData = @json($otcdCniData);
                        const today = new Date().getDate();

                        function initializeCharts() {
                            Object.keys(otcdCniData).forEach(model => {
                                const data = otcdCniData[model];
                                const plannedData = Array(31).fill(0);
                                const actualData = Array(31).fill(0);
                                const percentageAccuracy = Array(31).fill(0);
                                let totalPercentage = 0;
                                let count = 0;

                                data.forEach(entry => {
                                    const day = new Date(entry.date).getDate() - 1;
                                    plannedData[day] = parseInt(entry.total_planned_qty, 10);
                                    actualData[day] = parseInt(entry.total_actual_qty, 10);
                                    if (day < today) {
                                        const percentage = entry.total_planned_qty > 0 ? Math.min((entry.total_actual_qty / entry.total_planned_qty) * 100, 100) : 0;
                                        percentageAccuracy[day] = isFinite(percentage) ? percentage : 0;
                                        totalPercentage += percentageAccuracy[day];
                                        count++;
                                    }
                                });

                                createOTDCChart(model, plannedData, actualData, percentageAccuracy, 31);
                            });
                        }

                        $('#otdcCniCarousel').on('slid.bs.carousel', function () {
                            const activeSlide = $(this).find('.carousel-item.active');
                            const model = activeSlide.find('.chart-custom').attr('id').replace('otdc-chart-', '');
                            if (!activeSlide.data('chart-initialized')) {
                                activeSlide.data('chart-initialized', true);
                                initializeCharts();
                            }
                        });

                        initializeCharts(); // Initialize charts for the first slide
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
      console.log('Loading variant code quantities chart.');

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
        console.log('Loading item code quantities charts.');

        const itemCodeQuantities = @json($itemCodeQuantities);
        const month = new Date().toLocaleString('default', { month: 'long' });

        if (typeof itemCodeQuantities === 'object') {
            Object.keys(itemCodeQuantities).forEach((groupIndex) => {
                console.log('Processing item code group:', groupIndex);

                const group = itemCodeQuantities[groupIndex];

                const itemCodes = group.map(item => item.code);
                const quantities = group.map(item => item.qty);
                const itemIds = group.map(item => item._id); // Add this line to get item IDs

                console.log(`Item Codes for Group ${groupIndex}:`, itemCodes);
                console.log(`Quantities for Group ${groupIndex}:`, quantities);

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
