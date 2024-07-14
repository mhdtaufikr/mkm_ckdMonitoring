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
        height: 375px; /* Adjust the height as needed */
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
                <div class="col-md-6 mb-4">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>Inventory Monitoring</h4>
                        </div>
                        <div class="card-body">
                            <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($itemCodes as $itemCode => $comparisons)
                                        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div style="margin-top: -20px" class="carousel-inner">
                                    @foreach ($itemCodes as $itemCode => $comparisons)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <p class="text-center">{{ $itemCode }}</p>
                                            <div class="chart-container">
                                                <canvas id="chart-{{ $itemCode }}" class="chart-custom"></canvas>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Level Carousel -->
                <div class="col-md-6 mb-4">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>Stock Level</h4>
                        </div>
                        <div class="card-body">
                            <div id="stockLevelCarousel" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach ($stockLevels as $itemCode => $levels)
                                        <button type="button" data-bs-target="#stockLevelCarousel" data-bs-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : '' }}" aria-label="Slide {{ $loop->index + 1 }}"></button>
                                    @endforeach
                                </div>
                                <div style="margin-top: -20px" class="carousel-inner">
                                    @foreach ($stockLevels as $itemCode => $levels)
                                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                            <p class="text-center">{{ $itemCode }}</p>
                                            <div class="chart-container">
                                                <canvas id="stock-level-{{ $itemCode }}" class="chart-custom"></canvas>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#stockLevelCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#stockLevelCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>####</h4>
                        </div>
                        <div class="card-body">

                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card card-custom">
                        <div class="card-header">
                            <h4>####</h4>
                        </div>
                        <div class="card-body">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
    console.log('DOM fully loaded and parsed');

    const groupedComparisons = @json($itemCodes);
    const groupedStockLevels = @json($stockLevels);
    const month = new Date().toLocaleString('default', { month: 'long' });

    console.log('Grouped Comparisons:', groupedComparisons);
    console.log('Grouped Stock Levels:', groupedStockLevels);

    if (typeof groupedComparisons === 'object') {
        Object.keys(groupedComparisons).forEach((itemCode) => {
            console.log('Processing item code:', itemCode);

            const comparisons = groupedComparisons[itemCode];
            const plannedData = Array(31).fill(null);
            const actualData = Array(31).fill(null);
            const percentageDifference = Array(31).fill(0); // Default value set to 0%

            comparisons.forEach((comparison, index) => {
                const plannedDay = new Date(comparison.planned_receiving_date).getDate() - 1;
                const actualDay = new Date(comparison.receiving_date).getDate() - 1;
                plannedData[plannedDay] = comparison.planned_qty;
                actualData[actualDay] = comparison.received_qty;
            });

            console.log(`Planned Data for ${itemCode}:`, plannedData);
            console.log(`Actual Data for ${itemCode}:`, actualData);

            plannedData.forEach((value, index) => {
                if (value !== null && actualData[index] !== null) {
                    const percentage = ((actualData[index] / value) * 100);
                    percentageDifference[index] = percentage !== 0 ? percentage : 0; // Set to 0% if percentage is 0%
                }
            });

            console.log(`Percentage Difference for ${itemCode}:`, percentageDifference);

            const ctx = document.getElementById(`chart-${itemCode}`).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Array.from({ length: 31 }, (_, i) => (i + 1).toString()),
                    datasets: [{
                        label: 'Planned Stock',
                        data: plannedData,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)', // Increase opacity
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 1
                    },
                    {
                        label: 'Actual Stock',
                        data: actualData,
                        backgroundColor: 'rgba(255, 159, 64, 0.8)', // Increase opacity
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        type: 'bar',
                        order: 2
                    },
                    {
                        label: 'Percentage Accuracy',
                        data: percentageDifference,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false,
                        type: 'line',
                        yAxisID: 'y-axis-2',
                        order: 0,
                        borderWidth: 2 // Increase the line thickness here
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
                                minRotation: 0,
                                callback: function(value, index, values) {
                                    return (index + 1) % 4 === 0 || index === 0 ? (index + 1).toString() : '';
                                }
                            }
                        },
                        y: {
                            stacked: false,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Stock Quantity'
                            }
                        },
                        'y-axis-2': {
                            stacked: false,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Percentage'
                            },
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
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
                                    if (context.dataset.label === 'Percentage Accuracy') {
                                        return context.raw !== null && !isNaN(context.raw) ? context.raw.toFixed(2) + '%' : '';
                                    }
                                    return context.raw;
                                }
                            }
                        }
                    }
                }
            });
        });
    }

    if (typeof groupedStockLevels === 'object') {
        Object.keys(groupedStockLevels).forEach((itemCode) => {
            console.log('Processing stock level for item code:', itemCode);

            const levels = groupedStockLevels[itemCode];
            const stockData = Array(31).fill(null);

            levels.forEach((level, index) => {
                const day = new Date(level.date).getDate() - 1;
                stockData[day] = level.stock_level;
            });

            console.log(`Stock Data for ${itemCode}:`, stockData);

            const ctx = document.getElementById(`stock-level-${itemCode}`).getContext('2d');
            const myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.from({ length: 31 }, (_, i) => (i + 1).toString()),
                    datasets: [{
                        label: 'Stock Level',
                        data: stockData,
                        backgroundColor: 'rgba(75, 192, 192, 0.4)', // Increase opacity
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 3, // Increase line thickness
                        fill: true,
                        spanGaps: true // Ensure the line is continuous even with gaps
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            categoryPercentage: 0.5,
                            barPercentage: 0.5,
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0,
                                minRotation: 0,
                                callback: function(value, index, values) {
                                    return (index + 1) % 4 === 0 || index === 0 ? (index + 1).toString() : '';
                                }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Stock Level'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false,
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
                    }
                }
            });
        });
    }
});

</script>
@endsection
