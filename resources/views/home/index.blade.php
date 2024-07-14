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
        height: 100%;
        width: 100%;
    }
    .chart-custom {
        width: 100% !important;
        height: 275px !important; /* Adjust the height as needed */
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
                                            <canvas id="chart-{{ $itemCode }}" class="chart-size"></canvas>
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
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
</main>

<style>
    .chart-size {
        width: 100% !important;
        height: 275px  !important; /* Adjust the height as needed */
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        console.log('DOM fully loaded and parsed');

        const groupedComparisons = @json($itemCodes);

        console.log('Grouped Comparisons:', groupedComparisons);

        if (typeof groupedComparisons === 'object') {
            Object.keys(groupedComparisons).forEach((itemCode) => {
                console.log('Processing item code:', itemCode);

                const comparisons = groupedComparisons[itemCode];
                const plannedData = Array(31).fill(null);
                const actualData = Array(31).fill(null);

                comparisons.forEach((comparison, index) => {
                    const plannedDay = new Date(comparison.planned_receiving_date).getDate() - 1;
                    const actualDay = new Date(comparison.receiving_date).getDate() - 1;
                    plannedData[plannedDay] = comparison.planned_qty;
                    actualData[actualDay] = comparison.received_qty;
                });

                console.log(`Planned Data for ${itemCode}:`, plannedData);
                console.log(`Actual Data for ${itemCode}:`, actualData);

                const percentageDifference = plannedData.map((value, index) => {
                    if (value !== null && actualData[index] !== null) {
                        const percentage = ((actualData[index] / value) * 100);
                        return percentage !== 0 ? percentage : null; // Set to null if percentage is 0%
                    }
                    return null;
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
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1,
                            type: 'bar'
                        },
                        {
                            label: 'Actual Stock',
                            data: actualData,
                            backgroundColor: 'rgba(255, 159, 64, 0.2)',
                            borderColor: 'rgba(255, 159, 64, 1)',
                            borderWidth: 1,
                            type: 'bar'
                        },
                        {
                            label: 'Percentage Accuracy',
                            data: percentageDifference,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: false,
                            type: 'line',
                            yAxisID: 'y-axis-2'
                        }]
                    },
                    options: {
                        responsive: true,
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
                                    label: function(context) {
                                        if (context.dataset.label === 'Percentage Accuracy') {
                                            return context.raw !== null ? context.raw.toFixed(2) + '%' : '';
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
    });
</script>
@endsection
