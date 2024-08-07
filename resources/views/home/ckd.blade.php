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
    height: 400px; /* Adjust the height as needed */
    width: 140%; /* Use auto for dynamic width */
}
.chart-custom {
    width: 100% !important;
    height: 100% !important; /* Let the canvas take the full height of the container */
}

.card-custom {
    height: 450px; /* Adjust the height as needed */
    width: 100%; /* Adjust the width as needed */
}
    body {
        transform: scale(0.7);
        transform-origin: top left;
        width: 142.857%; /* 100 / 70 */
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
                    <div style="height: 905px" class="card card-custom">
                        <div class="card-header">
                            <h4>Variant Code Summary</h4>
                        </div>
                        <div class="card-body">
                            <div  id="variant-code-pie-chart" style=" width: 130%; height: 130%; margin-left: 20px;"></div>
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
                            <div style="margin-top: -20px; width: 140%; height: 130%; margin-left: 20px;" class="chart-container">
                                <div  class="chart-custom" id="chartdiv"></div>
                            </div>
                        </div>
                    </div>

                <!-- Chart code -->
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

                        // Process vendor data
                        const vendorData = @json($vendorData);
                        const data = vendorData.SENOPATI.map(item => ({
                            date: new Date(item.date).getDate().toString(), // Convert date to day of month as string
                            actual: parseInt(item.total_actual_qty),
                            plan: parseInt(item.total_planned_qty),
                            percentage: parseFloat(item.percentage) // Renamed from 'expenses' to 'percentage'
                        }));

                        // Create axes
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
                        xRenderer.grid.template.setAll({ location: 1 });
                        xAxis.data.setAll(data);

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
                                max: 100, // Adjust the max value to 100%
                                renderer: am5xy.AxisRendererY.new(root, { opposite: true, strokeOpacity: 0.1 })
                            })
                        );
                        yAxisRight.children.moveValue(am5.Label.new(root, {
                            rotation: -90,
                            text: "Percentage (%)",
                            y: am5.p50,
                            centerX: am5.p50
                        }), 0);

                        // Add Actual series
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

                        // Add Plan series
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

                        // Add Percentage series
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
                                stroke: am5.color(0x000000), // Set the color of the percentage line to black
                                fill: am5.color(0x000000) // Set the fill color if needed
                            })
                        );
                        percentageSeries.strokes.template.setAll({ strokeWidth: 3 });
                        percentageSeries.data.setAll(data);
                        percentageSeries.bullets.push(function (root, series, dataItem) {
                            var value = dataItem.dataContext.percentage;
                            var bulletColor = value < 100 ? am5.color(0xff0000) : am5.color(0x00ff00); // Red if below 100%, green if 100% or more
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

                            // Log trend line data to console
                            console.log("Trend Line Data:", data);

                            series.data.setAll(data);
                            series.strokes.template.setAll({ stroke: color, strokeWidth: 4, strokeDasharray: [5, 5] });
                            series.appear(1000, 100);
                        }

                        // Create trend line based on percentage data
                        var trendLineData = data.map((item) => ({ date: item.date, value: item.percentage }));
                        console.log("Mapped Trend Line Data:", trendLineData);
                        createTrendLine(trendLineData, root.interfaceColors.get("positive"));

                        chart.set("cursor", am5xy.XYCursor.new(root, {
                            behavior: "none" // Disable cursor behavior to avoid misalignment
                        }));

                        // Add legend
                        var legend = chart.children.push(
                            am5.Legend.new(root, {
                                centerX: am5.p50,
                                x: am5.p50
                            })
                        );
                        legend.data.setAll(chart.series.values);

                        // Make stuff animate on load
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
                            <div class="carousel-indicators">
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
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#itemCodeQuantityCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
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

        // Create the combined chart
        am5.ready(function() {
          // Create root element
          var root = am5.Root.new("variant-code-pie-chart");

          // Set themes
          root.setThemes([am5themes_Animated.new(root)]);

          // Create chart
          var chart = root.container.children.push(
            am5percent.PieChart.new(root, {
              layout: root.verticalLayout,
              innerRadius: am5.percent(50)
            })
          );

          // Create series
          var series = chart.series.push(
            am5percent.PieSeries.new(root, {
              name: "Series",
              valueField: "value",
              categoryField: "category"
            })
          );

          // Set data
          series.data.setAll(combinedData);

          // Configure labels
          series.labels.template.set("text", "{category}: {value}");

          // Configure tooltips
          series.slices.template.set("tooltipText", "{category}: {value}");

          // Add legend
          var legend = chart.children.push(am5.Legend.new(root, {
            centerX: am5.p50,
            x: am5.p50
          }));

          legend.data.setAll(series.dataItems);

          // Animate chart
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
