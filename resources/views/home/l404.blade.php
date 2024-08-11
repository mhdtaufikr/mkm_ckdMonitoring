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
                <!-- OTDC All Vendor -->
                <div class="col-md-6 mb-2">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>OTDC All Vendor</h4>
                        </div>
                        <div class="card-body">
                            @php
                            $totalPercentage = 0;
                            $count = 0;
                            $today = now()->format('Y-m-d');

                            foreach ($vendorDataAggregate as $entry) {
                                if ($entry->date <= $today) {
                                    if ($entry->total_planned_qty > 0) {
                                        $percentage = min(($entry->total_actual_qty / $entry->total_planned_qty) * 100, 100);
                                    } elseif ($entry->total_actual_qty > 0) {
                                        $percentage = 100;
                                    } else {
                                        $percentage = 0;
                                    }
                                    $totalPercentage += $percentage;
                                    $count++;
                                }
                            }
                            $averagePercentageAllVendor = ($count > 0) ? $totalPercentage / $count : 0;
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
                                            <td>{{ number_format($averagePercentageAllVendor, 2) }}%</td>
                                            <td>
                                                <span id="signal-otdc" class="signal
                                                    {{ $averagePercentageAllVendor >= 95 ? 'green' : ($averagePercentageAllVendor >= 85 ? 'yellow' : 'red') }}">
                                                    {{ $averagePercentageAllVendor >= 95 ? 'G' : ($averagePercentageAllVendor >= 85 ? 'Y' : 'R') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div style="margin-top: -20px" class="chart-container">
                                <div id="chartdiv" class="chart-custom" style="width: 100%; height: 100%;"></div>

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


                <div class="col-md-6 mb-2">
                    <div style="height: 375px" class="card card-custom">
                        <div class="card-header">
                            <h4>Vendor Monthly Summary</h4>
                        </div>
                        <div class="card-body">
                            <div id="vendorSummaryCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div hidden class="carousel-indicators">
                                    <!-- Carousel indicators will be populated dynamically -->
                                </div>
                                <div class="carousel-inner">
                                    <!-- Carousel items will be populated dynamically -->
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#vendorSummaryCarousel" data-bs-slide="prev">
                                    <span hidden class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span hidden class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#vendorSummaryCarousel" data-bs-slide="next">
                                    <span hidden class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span hidden class="visually-hidden">Next</span>
                                </button>
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
        // Create root element
        var root = am5.Root.new("chartdiv");

        // Set themes
        root.setThemes([am5themes_Animated.new(root)]);

        // Create chart
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

        // Data from your backend
        const vendorDataAggregate = @json($vendorDataAggregate);
        const daysOfMonth = Array.from({ length: 31 }, (_, i) => (i + 1).toString());

        const data = daysOfMonth.map(day => {
            const entry = vendorDataAggregate.find(item => new Date(item.date).getDate().toString() === day);
            return {
                date: day,
                actual: entry ? parseInt(entry.total_actual_qty) : 0,
                plan: entry ? parseInt(entry.total_planned_qty) : 0,
                percentage: entry ? parseFloat(entry.percentage) : 0
            };
        });

        // Predefine x-axis categories to 1-31
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
                max: 120, // Cap the percentage at 120%
                strictMinMax: true, // Ensure min and max are strictly followed
                extraMax: 0, // Disable extra max
                renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
            })
        );

        yAxisRight.children.moveValue(am5.Label.new(root, {
            rotation: -90,
            text: "Percentage (%)",
            y: am5.p50,
            centerX: am5.p50
        }), 0);

        // Plan series
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

        // Actual series
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

        // Percentage series
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

        // Add legend
        var legend = chart.children.push(
            am5.Legend.new(root, {
                centerX: am5.p50,
                x: am5.p50
            })
        );
        legend.data.setAll(chart.series.values);

        // Enable cursor and tooltips for all series
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
    });
</script>


<script>
    am5.ready(function() {
        const vendorData = @json($vendorData);
        const today = new Date().getDate(); // Get today's date
        const daysOfMonth = Array.from({ length: 31 }, (_, i) => (i + 1).toString());

        // Iterate over each vendor and create a chart
        Object.keys(vendorData).forEach((vendorName) => {
            const data = vendorData[vendorName];

            // Prepare data arrays
            const plannedData = Array(31).fill(0);
            const actualData = Array(31).fill(0);
            const percentageDifference = Array(31).fill(0);

            data.forEach((comparison) => {
                const day = new Date(comparison.date).getDate() - 1;
                plannedData[day] += parseInt(comparison.total_planned_qty);
                actualData[day] += parseInt(comparison.total_actual_qty);
            });

            plannedData.forEach((value, index) => {
                if (value > 0) {
                    const percentage = Math.min((actualData[index] / value) * 100, 100);
                    percentageDifference[index] = isFinite(percentage) ? percentage : 0;
                }
            });

            // Create a unique root for each vendor's chart
            var root = am5.Root.new(`otdc-chart-${vendorName}`);

            // Set themes
            root.setThemes([am5themes_Animated.new(root)]);

            // Create chart for each vendor
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

            // X-axis
            var xAxis = chart.xAxes.push(
                am5xy.CategoryAxis.new(root, {
                    categoryField: "date",
                    tooltip: am5.Tooltip.new(root, {}),
                    renderer: am5xy.AxisRendererX.new(root, {
                        minGridDistance: 30
                    })
                })
            );
            xAxis.data.setAll(daysOfMonth.map(date => ({ date })));

            // Y-axis for quantity (left)
            var yAxis = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    min: 0,
                    extraMax: 0.1,
                    renderer: am5xy.AxisRendererY.new(root, { strokeOpacity: 0.1 })
                })
            );

            // Add a label to the left Y-axis
            yAxis.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Quantity",  // Label text for quantity
                y: am5.p50,
                centerX: am5.p50
            }), 0);

            // Y-axis for percentage (right)
            var yAxisRight = chart.yAxes.push(
                am5xy.ValueAxis.new(root, {
                    min: 0,
                    max: 120, // Cap the percentage at 120%
                    strictMinMax: true, // Ensure min and max are strictly followed
                    extraMax: 0, // Disable extra max
                    renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
                })
            );

            // Add a label to the right Y-axis
            yAxisRight.children.moveValue(am5.Label.new(root, {
                rotation: -90,
                text: "Percentage (%)",  // Label text for percentage
                y: am5.p50,
                centerX: am5.p50
            }), 0);


            // Plan series
            var planSeries = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    name: "Planned Qty",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "plan",
                    categoryXField: "date",
                    clustered: true,
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{name}: {valueY}"
                    })
                })
            );
            planSeries.columns.template.setAll({ fill: am5.color("#36A2EB"), width: am5.percent(80) });
            planSeries.data.setAll(daysOfMonth.map((date, i) => ({ date, plan: plannedData[i] })));

            // Actual series
            var actualSeries = chart.series.push(
                am5xy.ColumnSeries.new(root, {
                    name: "Actual Qty",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "actual",
                    categoryXField: "date",
                    clustered: true,
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{name}: {valueY}"
                    })
                })
            );
            actualSeries.columns.template.setAll({ fill: am5.color("#FF9F40"), width: am5.percent(80) });
            actualSeries.data.setAll(daysOfMonth.map((date, i) => ({ date, actual: actualData[i] })));

            // Percentage series
            var percentageSeries = chart.series.push(
                am5xy.LineSeries.new(root, {
                    name: "Percentage Accuracy",
                    xAxis: xAxis,
                    yAxis: yAxisRight,
                    valueYField: "percentage",
                    categoryXField: "date",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{name}: {valueY}%"
                    }),
                    stroke: am5.color(0x000000),
                    fill: am5.color(0x000000)
                })
            );
            percentageSeries.strokes.template.setAll({ strokeWidth: 3 });
            percentageSeries.data.setAll(daysOfMonth.map((date, i) => ({ date, percentage: percentageDifference[i] })));

            // Add bullets for the percentage series
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
        });
    });
</script>




<!-- Script for Vendor Monthly Summary Chart -->
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
    const vendors = @json($vendors);
    const totalPlanned = @json($totalPlanned);
    const totalActual = @json($totalActual);
    const chunkSize = 5; // Split vendors into groups of 5
    const vendorDataChunks = [];

    // Split data into chunks
    for (let i = 0; i < vendors.length; i += chunkSize) {
        vendorDataChunks.push({
            vendors: vendors.slice(i, i + chunkSize),
            planned: totalPlanned.slice(i, i + chunkSize),
            actual: totalActual.slice(i, i + chunkSize),
        });
    }

    const carouselIndicators = document.querySelector('#vendorSummaryCarousel .carousel-indicators');
    const carouselInner = document.querySelector('#vendorSummaryCarousel .carousel-inner');

    vendorDataChunks.forEach((chunk, index) => {
        // Create a new carousel item
        const carouselItem = document.createElement('div');
        carouselItem.className = `carousel-item ${index === 0 ? 'active' : ''}`;

        const chartDiv = document.createElement('div');
        chartDiv.style.height = "300px";
        carouselItem.appendChild(chartDiv);

        carouselInner.appendChild(carouselItem);

        const indicatorButton = document.createElement('button');
        indicatorButton.type = 'button';
        indicatorButton.dataset.bsTarget = '#vendorSummaryCarousel';
        indicatorButton.dataset.bsSlideTo = index;
        indicatorButton.className = `${index === 0 ? 'active' : ''}`;
        indicatorButton.ariaLabel = `Slide ${index + 1}`;
        carouselIndicators.appendChild(indicatorButton);

        // Create chart for this chunk
        const canvas = document.createElement('canvas');
        chartDiv.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chunk.vendors,
                datasets: [{
                    label: 'Planned',
                    data: chunk.planned,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    type: 'bar'
                },
                {
                    label: 'Actual',
                    data: chunk.actual,
                    backgroundColor: 'rgba(255, 159, 64, 0.8)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1,
                    type: 'bar'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', // Horizontal bar chart
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
                        position: 'left',
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
                                title += ` ${month}`;
                                return title;
                            },
                            label: function (context) {
                                let label = `${context.dataset.label || ''}: `;
                                if (typeof context.raw === 'number') {
                                    label += context.raw.toFixed(0); // Show quantity with no decimal places
                                }
                                return label;
                            },
                            afterLabel: function (context) {
                                const plannedIndex = context.datasetIndex === 0 ? context.dataIndex : null;
                                const actualIndex = context.datasetIndex === 1 ? context.dataIndex : null;
                                if (plannedIndex !== null) {
                                    const plannedValue = context.chart.data.datasets[0].data[plannedIndex];
                                    const actualValue = context.chart.data.datasets[1].data[plannedIndex];
                                    return ` Planned: ${plannedValue}`;
                                } else if (actualIndex !== null) {
                                    const plannedValue = context.chart.data.datasets[0].data[actualIndex];
                                    const actualValue = context.chart.data.datasets[1].data[actualIndex];
                                    return ` Actual: ${actualValue}`;
                                }
                                return null;
                            }
                        }
                    }
                }
            }
        });
    });
});

</script>

<!-- Script for Item Code Quantity Chart -->
<script>
document.addEventListener('DOMContentLoaded', (event) => {
    console.log('DOM fully loaded and parsed');

    const itemCodeQuantities = @json($itemCodeQuantities);
    const month = new Date().toLocaleString('default', { month: 'long' });

    console.log('Item Code Quantities:', itemCodeQuantities);

    if (typeof itemCodeQuantities === 'object') {
        Object.keys(itemCodeQuantities).forEach((groupIndex) => {
            const group = itemCodeQuantities[groupIndex];
            const itemCodes = group.map(item => item.code);
            const quantities = group.map(item => item.qty);
            const itemIds = group.map(item => item._id); // Assuming you have the IDs available

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
                                title: function (tooltipItems) {
                                    let title = tooltipItems[0].label || '';
                                    title += ` ${month}`;
                                    return title;
                                },
                                label: function (context) {
                                    return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) : '';
                                }
                            }
                        }
                    },
                    onClick: function (e, elements) {
                if (elements.length > 0) {
                    const elementIndex = elements[0].index;
                    const itemId = itemIds[elementIndex];
                    const url = `/inventory/${itemId}/details`;
                    window.open(url, '_blank');
                }
            }
                }
            });
        });
    }
});
</script>

<!-- Refresh Page Script -->
<script>
function refreshPage() {
    setTimeout(function() {
        location.reload();
    }, 200000); // 200000 milliseconds = 5 minutes
}

// Call the function when the page loads
refreshPage();
</script>

@endsection
