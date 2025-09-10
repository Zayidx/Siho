<div>
    <div class="page-heading">
        <h3>Reporting & Analytics</h3>
        <p class="text-muted">View reports on revenue and occupancy.</p>
    </div>
    <div class="page-content">
        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Select Date Range</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="startDate">Start Date</label>
                                <input type="date" class="form-control" id="startDate" wire:model="startDate">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="endDate">End Date</label>
                                <input type="date" class="form-control" id="endDate" wire:model="endDate">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button wire:click="generateReport" class="btn btn-primary w-100">Generate</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Revenue Report</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Occupancy Rate Report (%)</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="occupancyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </section>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            let revenueChartInstance;
            let occupancyChartInstance;

            const renderCharts = () => {
                // Destroy existing charts if they exist
                if (revenueChartInstance) {
                    revenueChartInstance.destroy();
                }
                if (occupancyChartInstance) {
                    occupancyChartInstance.destroy();
                }

                // Revenue Chart
                const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                const revenueData = @json($revenueData);
                revenueChartInstance = new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: revenueData.labels,
                        datasets: [{
                            label: 'Total Revenue (Rp)',
                            data: revenueData.values,
                            backgroundColor: 'rgba(67, 94, 190, 0.2)',
                            borderColor: 'rgba(67, 94, 190, 1)',
                            borderWidth: 1,
                            fill: true,
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });

                // Occupancy Chart
                const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
                const occupancyData = @json($occupancyData);
                occupancyChartInstance = new Chart(occupancyCtx, {
                    type: 'bar',
                    data: {
                        labels: occupancyData.labels,
                        datasets: [{
                            label: 'Occupancy Rate (%)',
                            data: occupancyData.values,
                            backgroundColor: 'rgba(243, 97, 109, 0.8)',
                            borderColor: 'rgba(243, 97, 109, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }

            // Initial render
            renderCharts();

            // Re-render charts when the component dispatches the event
            Livewire.on('reportGenerated', () => {
                renderCharts();
            });
        });
    </script>
@endpush
