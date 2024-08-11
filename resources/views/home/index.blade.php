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
        height: 275px; /* Adjust the height as needed */
        width: 100%;
    }
    .chart-custom {
        width: 100% !important;
        height: 100% !important; /* Let the canvas take the full height of the container */
    }
    .card-custom {
        height: 450px; /* Adjust the height as needed */
        width: 100%; /* Adjust the width as needed */
    }

    .nav-fixed #layoutSidenav #layoutSidenav_nav {
        width: 15rem;
        height: 250vh;
        z-index: 1038;
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
                        <h1 class="page-header-title">
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="content">
        <div class="container-fluid px-4 mt-n10">
            <div class="row">
                 <!-- Inventory Monitoring Carousel -->
                 <div class="col-md-6 mb-2">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>Inventory Monitoring</h4>
                        </div>
                        <div class="card-body">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div hidden class="carousel-indicators">
                                    @foreach ($plannedData as $itemCode => $comparisons)
                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div  class="carousel-inner">
                                    @foreach ($plannedData as $itemCode => $comparisons)

                                    @php
                                    // Mengambil data dari database
                                    $comparisons = DB::table('inventory_comparison')
                                        ->where('id_location', $locationId)
                                        ->where('inventory_id', $comparisons[0]->inventory_id )
                                        ->get();

                                    // Inisialisasi variabel untuk menghitung total persentase dan jumlah entri
                                    $totalPercentage = 0;
                                    $count = 0;
                                    $today = now()->format('Y-m-d');

                                    // Loop melalui setiap entri dalam $comparisons
                                    foreach ($comparisons as $comparison) {
                                        // Memastikan bahwa hanya data hingga hari ini yang dihitung
                                        if ($comparison->receiving_date <= $today) {
                                            $totalPercentage += $comparison->percentage;
                                            $count++;
                                        }
                                    }
                                    // Menghitung rata-rata persentase
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
                                                            <th>Average Inventory</th>
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
                                            <p style="margin-top: -20px" class="text-center">{{ $itemCode }}</p>
                                            <div style="margin-top: -20px" class="chart-container">
                                                <div id="chart-{{ $itemCode }}" class="chart-custom"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                    <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span hidden class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                    <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span hidden class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>


              <!-- OTDC Chart Carousel -->
            <div class="col-md-6 mb-2">
                <div class="card card-custom">
                    <div class="card-header">
                        <h4>OTDC</h4>
                    </div>
                    <div class="card-body">
                        <div id="otdcCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div hidden class="carousel-indicators">
                                @foreach ($vendorData as $vendorName => $data)
                                    <button type="button" data-bs-target="#otdcCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                @endforeach
                            </div>
                            <div class="carousel-inner">
                                @foreach ($vendorData as $vendorName => $data)
                                    @php
                                        $totalPercentage = 0;
                                        $count = 0;
                                        $today = now()->format('Y-m-d');
                                        foreach ($data as $entry) {
                                            if ($entry->date <= $today) {
                                                $totalPercentage += $entry->percentage;
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
                                        <p style="margin-top: -20px" class="text-center">{{ $vendorName }}</p>
                                        <div style="margin-top: -20px" class="chart-container">
                                            <div id="otdc-chart-{{ $vendorName }}" class="chart-custom"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#otdcCarousel" data-bs-slide="prev">
                                <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#otdcCarousel" data-bs-slide="next">
                                <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span hidden class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>


                <!-- Vendor Monthly Summary Chart -->
            <div class="col-md-6 mb-2">
                <div style="height: 375px" class="card card-custom">
                    <div class="card-header">
                        <h4>Vendor Monthly Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="vendorSummaryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>


               <!-- Item Code Quantity Carousel -->
<div class="col-md-6 mb-2">
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
                            <div class="chart-container">
                                <canvas id="item-code-quantity-chart-{{ $groupIndex }}" class="chart-custom"></canvas>
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
        <!-- /.container-fluid -->
    </section>
</main>
<script>
    $(document).ready(function() {
      var table = $("#tableUser").DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        // "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
      });
    });
  </script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-trendline"></script>
<script>
    am5.ready(function() {
        // Initialize necessary shared data
        const groupedPlannedData = @json($plannedData);
        const groupedActualData = @json($actualData);
        const today = new Date().getDate(); // Get today's date

        // Function to create a chart for each itemCode
        function createInventoryMonitoringChart(itemCode) {
            const plannedComparisons = groupedPlannedData[itemCode];
            const actualComparisons = groupedActualData[itemCode] || [];

            const plannedData = Array(31).fill(null);
            const actualData = Array(31).fill(null);
            const percentageDifference = Array(31).fill(0);
            const percentageTrendData = Array(today).fill(0);

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
                    if (index < today) {
                        percentageTrendData[index] = percentageDifference[index];
                    }
                }
            });

            // Create amCharts chart instance
            var root = am5.Root.new(`chart-${itemCode}`);

            // Set themes
            root.setThemes([am5themes_Animated.new(root)]);

            // Create the chart
            var chart = root.container.children.push(
                am5xy.XYChart.new(root, {
                    panX: false,
                    panY: false,
                    wheelX: "none",
                    wheelY: "none",
                    layout: root.verticalLayout
                })
            );

            // Create X-axis
            var xAxis = chart.xAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "date",
                    renderer: am5xy.AxisRendererX.new(root, {
                        minGridDistance: 30
                    })
                })
            );
            xAxis.data.setAll(Array.from({ length: 31 }, (_, i) => ({ date: (i + 1).toString() })));

            // Y-axis for quantity
            var yAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    min: 0,
                    extraMax: 0.1,
                    renderer: am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 })
                })
            );

            // Add Y-axis label
            yAxis.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Quantity",
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            // Y-axis for percentage
            var yAxisRight = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    min: 0,
                    max: 120,
                    strictMinMax: true,
                    renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
                })
            );

            // Add Y-axis label
            yAxisRight.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Percentage (%)",
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            // Plan series
            var planSeries = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    name: "Planned Stock",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "plan",
                    categoryXField: "date",
                    tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
                })
            );
            planSeries.columns.template.setAll({ fill: am5.color("#36A2EB"), width: am5.percent(80) });
            planSeries.data.setAll(plannedData.map((value, i) => ({ date: (i + 1).toString(), plan: value })));

            // Actual series
            var actualSeries = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    name: "Actual Stock",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "actual",
                    categoryXField: "date",
                    tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}" })
                })
            );
            actualSeries.columns.template.setAll({ fill: am5.color("#FF9F40"), width: am5.percent(80) });
            actualSeries.data.setAll(actualData.map((value, i) => ({ date: (i + 1).toString(), actual: value })));

            // Percentage series
            var percentageSeries = chart.series.push(
                am5xy.LineSeries.new(root, {
                    name: "Percentage Accuracy",
                    xAxis: xAxis,
                    yAxis: yAxisRight,
                    valueYField: "percentage",
                    categoryXField: "date",
                    tooltip: am5.Tooltip.new(root, { labelText: "{name}: {valueY}%" }),
                    stroke: am5.color(0x000000),
                    fill: am5.color(0x000000)
                })
            );
            percentageSeries.strokes.template.setAll({ strokeWidth: 3 });
            percentageSeries.data.setAll(percentageDifference.map((value, i) => ({ date: (i + 1).toString(), percentage: value })));

            // Add bullets for percentage series
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

            // Add legend
            var legend = chart.children.push(
                am5.Legend.new(root, {
                    centerX: am5.p50,
                    x: am5.p50
                })
            );
            legend.data.setAll(chart.series.values);

            // Enable cursor
            var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {
                behavior: "none",
                xAxis: xAxis
            }));
            cursor.lineY.set("visible", false);

            // Make everything animate on load
            chart.appear(1000, 100);
            actualSeries.appear();
            planSeries.appear();
            percentageSeries.appear();
        }

        // Loop through each itemCode to create charts
        Object.keys(groupedPlannedData).forEach(itemCode => {
            createInventoryMonitoringChart(itemCode);
        });
    });
</script>


<script>
    am5.ready(function() {
        function createOTDCChart(vendorName, plannedData, actualData, percentageAccuracy, percentageTrendData, endDate) {
            var root = am5.Root.new(`otdc-chart-${vendorName}`);

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

            // Add Y-axis label
            yAxis.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Quantity",
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            // Add Y-axis label
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

        const vendorData = @json($vendorData);
        const today = new Date().getDate();

        if (typeof vendorData === 'object') {
            Object.keys(vendorData).forEach(vendorName => {
                const data = vendorData[vendorName];
                const plannedData = Array(31).fill(0);
                const actualData = Array(31).fill(0);
                const percentageAccuracy = Array(31).fill(0);
                const percentageTrendData = Array(today).fill(0);
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
                        percentageTrendData[day] = percentageAccuracy[day];
                    }
                });

                const averagePercentage = count > 0 ? totalPercentage / count : 0;
                createOTDCChart(vendorName, plannedData, actualData, percentageAccuracy, percentageTrendData, 31);
            });
        }
    });
</script>


<script>
    function createVendorSummaryChart(vendors, totalPlanned, totalActual) {
        const ctx = document.getElementById('vendorSummaryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: vendors,
                datasets: [
                    {
                        label: 'Planned',
                        data: totalPlanned,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Actual',
                        data: totalActual,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Swapping the axes for horizontal bars
                scales: {
                    x: {
                        stacked: false,
                        categoryPercentage: 0.5,
                        barPercentage: 0.5,
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    },
                    y: {
                        stacked: false,
                        title: {
                            display: true,
                            text: 'Vendor'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function (tooltipItems) {
                                let title = tooltipItems[0].label || '';
                                title += ` ${new Date().toLocaleString('default', { month: 'long' })}`;
                                return title;
                            },
                            label: function (context) {
                                return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(0) : '';
                            }
                        }
                    }
                }
            }
        });
    }

    // Call the function with your data
    document.addEventListener('DOMContentLoaded', function() {
        const vendors = @json($vendors);
        const totalPlanned = @json($totalPlanned);
        const totalActual = @json($totalActual);

        createVendorSummaryChart(vendors, totalPlanned, totalActual);
    });
</script>

<script>
            document.addEventListener('DOMContentLoaded', (event) => {
            const groupedPlannedData = @json($plannedData);
            const groupedActualData = @json($actualData);
            const vendorData = @json($vendorData);
            const itemCodeQuantities = @json($itemCodeQuantities);
            const vendors = @json($vendors);
            const totalPlanned = @json($totalPlanned);
            const totalActual = @json($totalActual);
            const today = new Date().getDate();

            // Create Inventory Monitoring Charts
            if (typeof groupedPlannedData === 'object' && typeof groupedActualData === 'object') {
                Object.keys(groupedPlannedData).forEach((itemCode) => {
                    createInventoryMonitoringChart(groupedPlannedData, groupedActualData, itemCode, today);
                });
            }

            // Create OTDC Charts
            if (typeof vendorData === 'object') {
                Object.keys(vendorData).forEach((vendorName) => {
                    const data = vendorData[vendorName];
                    const plannedData = Array(31).fill(null);
                    const actualData = Array(31).fill(null);
                    const percentageAccuracy = Array(31).fill(0);
                    const percentageTrendData = Array(today).fill(0);
                    let totalPercentage = 0;
                    let count = 0;

                    data.forEach((entry) => {
                        const day = new Date(entry.date).getDate() - 1;
                        plannedData[day] = entry.total_planned_qty;
                        actualData[day] = entry.total_actual_qty;
                        if (day < today) {
                            const percentage = entry.total_planned_qty > 0 ? Math.min((entry.total_actual_qty / entry.total_planned_qty) * 100, 100) : 0;
                            percentageAccuracy[day] = isFinite(percentage) ? percentage : 0;
                            totalPercentage += percentageAccuracy[day];
                            count++;
                            percentageTrendData[day] = percentageAccuracy[day];
                        }
                    });

                    const averagePercentage = count > 0 ? totalPercentage / count : 0;
                    createOTDCChart(vendorName, plannedData, actualData, percentageAccuracy, percentageTrendData, 31);
                });
            }

            // Create Vendor Monthly Summary Chart
            createVendorSummaryChart(vendors, totalPlanned, totalActual);

            // Create Item Code Quantities Charts
            if (typeof itemCodeQuantities === 'object') {
                Object.keys(itemCodeQuantities).forEach((groupIndex) => {
                    const group = itemCodeQuantities[groupIndex];
                    const itemCodes = group.map(item => item.code);
                    const quantities = group.map(item => item.qty);
                    createItemCodeQuantitiesChart(groupIndex, itemCodes, quantities);
                });
            }
        });

</script>
<script>
    function createItemCodeQuantitiesChart(groupIndex, itemCodes, quantities) {
        const ctx = document.getElementById(`item-code-quantity-chart-${groupIndex}`).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: itemCodes,
                datasets: [{
                    label: 'Quantity',
                    data: quantities,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
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
                        title: {
                            display: true,
                            text: 'Quantity'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            title: function (tooltipItems) {
                                let title = tooltipItems[0].label || '';
                                title += ` ${new Date().toLocaleString('default', { month: 'long' })}`;
                                return title;
                            },
                            label: function (context) {
                                return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) : '';
                            }
                        }
                    }
                }
            }
        });
    }

    // Call the function with your data
    document.addEventListener('DOMContentLoaded', function() {
        const itemCodeQuantities = @json($itemCodeQuantities);

        Object.keys(itemCodeQuantities).forEach(groupIndex => {
            const group = itemCodeQuantities[groupIndex];
            const itemCodes = group.map(item => item.code);
            const quantities = group.map(item => item.qty);

            createItemCodeQuantitiesChart(groupIndex, itemCodes, quantities);
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
