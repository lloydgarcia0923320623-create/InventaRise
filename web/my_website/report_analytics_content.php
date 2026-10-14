<h2>📊 Report Analytics Dashboard</h2>

<div class="report_grid fade-in">
    <div class="card chart_card">
        <h3>📦 Monthly Deliveries</h3>
        <canvas id="analyticsDeliveryChart"></canvas>
    </div>

    <div class="card chart_card">
        <h3>⚠️ Low Stock Items</h3>
        <canvas id="analyticsStockChart"></canvas>
    </div>

    <div class="card chart_card">
        <h3>💰 Revenue Overview</h3>
        <canvas id="analyticsRevenueChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
(async () => {
    try {
        const deliveryChart = document.getElementById('analyticsDeliveryChart');
        const stockChart = document.getElementById('analyticsStockChart');
        const revenueChart = document.getElementById('analyticsRevenueChart');

        if (!deliveryChart || !stockChart || !revenueChart) {
            return;
        }

        const res = await fetch("helpers/report_analytics_data.php?rand=" + Date.now());
        const json = await res.json();

        if (json.status !== "success") throw new Error(json.message);

        const { low_stock, monthly_deliveries, revenue_overview } = json.data;

        const deliveryLabels = monthly_deliveries.map(m => m.month);
        const deliveryData = monthly_deliveries.map(m => m.deliveries);

        new Chart(deliveryChart, {
            type: 'line',
            data: {
                labels: deliveryLabels.length ? deliveryLabels : ['No Data'],
                datasets: [{
                    label: 'Deliveries',
                    data: deliveryData.length ? deliveryData : [0],
                    borderColor: '#00d4ff',
                    backgroundColor: 'rgba(0,212,255,0.3)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        const stockLabels = low_stock.map(i => i.item);
        const stockQty = low_stock.map(i => i.total_qty);

        new Chart(stockChart, {
            type: 'bar',
            data: {
                labels: stockLabels.length ? stockLabels : ['No Low Stock Items'],
                datasets: [{
                    label: 'Quantity',
                    data: stockQty.length ? stockQty : [0],
                    backgroundColor: stockQty.map(q => q <= 2 ? '#d00000' : '#ffb703')
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });

        const revenueLabels = revenue_overview.map(r => r.month);
        const revenueData = revenue_overview.map(r => r.revenue);

        new Chart(revenueChart, {
            type: 'doughnut',
            data: {
                labels: revenueLabels.length ? revenueLabels : ['Deliveries'],
                datasets: [{
                    data: revenueData.length ? revenueData : [1],
                    backgroundColor: ['#00b4d8', '#0077b6', '#90e0ef', '#48cae4', '#0096c7']
                }]
            }
        });

    } catch (err) {
        console.error("Analytics load failed:", err);
        const grid = document.querySelector(".report_grid");
        if (grid) {
            grid.innerHTML = "<p style='color: #ffb703;'>⚠️ Failed to load analytics data.</p>";
        }
    }
})();
</script>
