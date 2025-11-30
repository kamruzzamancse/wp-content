<div class="dashboard-top">
    <div class="dashboard-top-left">
        <div class="tpg-dashboard-container">
            <div class="tpg-tracking-section">
                <div class="tpg-tracking-header">
                    <h1 class="tpg-section-title">Tracking Property</h1>
                    <div class="tpg-tracking-summary">
                        <span class="tpg-rental" id="tpg-avg-rental-price">Avg Rental: $0</span> | 
                        <span class="tpg-sales" id="tpg-avg-sales-price">Avg Sales: $0</span>
                    </div>
                    <select id="tpg-property-select">
                        <option value="">Loading...</option>
                    </select>
                </div>

                <div class="tpg-chart-container">
                    <div class="tpg-y-axis"></div>
                    <svg class="tpg-line-chart" viewBox="0 0 600 250" preserveAspectRatio="none"></svg>

                    <!-- Legend stays inside chart (right bottom) -->
                    <div class="tpg-chart-legend">
                        <span style="display:flex; align-items:center; gap:5px;">
                            <span style="width:12px; height:12px; background:#4e6ef2; display:inline-block;"></span> Rental Income
                        </span>
                        <span style="display:flex; align-items:center; gap:5px;">
                            <span style="width:12px; height:12px; background:#e74c3c; display:inline-block;"></span> Sales Price
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rentalDisplay = document.getElementById('tpg-avg-rental-price');
    const salesDisplay  = document.getElementById('tpg-avg-sales-price');
    const propertySelect = document.getElementById('tpg-property-select');
    const svg = document.querySelector('.tpg-line-chart');
    const yAxisDiv = document.querySelector('.tpg-y-axis');
    const chartWidth = 600;
    const chartHeight = 250;
    const leftPadding = 50;
    const rightPadding = 50;
    const topPadding = 30;
    const bottomPadding = 50;

    fetch('<?php echo admin_url("admin-ajax.php?action=get_rentcast_chart_data"); ?>')
        .then(res => res.json())
        .then(data => {
            const properties = data.properties || [];
            if (!properties.length) {
                propertySelect.innerHTML = '<option value="">No properties assigned</option>';
                return;
            }

            // Populate dropdown
            propertySelect.innerHTML = '';
            properties.forEach(prop => {
                const opt = document.createElement('option');
                opt.value = prop.id;
                opt.textContent = prop.address;
                propertySelect.appendChild(opt);
            });

            function drawYAxis(max) {
                yAxisDiv.innerHTML = '';
                for (let i = 5; i >= 0; i--) {
                    const span = document.createElement('span');
                    span.textContent = `$${Math.round(max * i / 5)}`;
                    yAxisDiv.appendChild(span);
                }
            }

            function updateChart(selectedId) {
                const selected = properties.filter(p => p.id == selectedId);
                if (!selected.length) return;

                svg.innerHTML = '';
                rentalDisplay.textContent = `Avg Rental: $${data.avg_rental}`;
                salesDisplay.textContent  = `Avg Sales: $${data.avg_sales}`;

                const points = [];
                const salesPoints = [];
                const usableWidth = chartWidth - leftPadding - rightPadding;
                const stepX = usableWidth / (properties.length - 1 || 1);
                const yAxisMax = data.y_axis_max;
                const usableHeight = chartHeight - topPadding - bottomPadding;

                drawYAxis(yAxisMax);

                properties.forEach((p, idx) => {
                    const x = leftPadding + idx * stepX;

                    // Map rental and sales values to Y-axis
                    const rentalY = topPadding + ((yAxisMax - p.rental) / yAxisMax) * usableHeight;
                    const salesY  = topPadding + ((yAxisMax - p.sales) / yAxisMax) * usableHeight;

                    // Draw circles
                    const rc = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    rc.setAttribute("cx", x);
                    rc.setAttribute("cy", rentalY);
                    rc.setAttribute("r", 6);
                    rc.setAttribute("fill", "#4e6ef2");
                    svg.appendChild(rc);

                    const sc = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    sc.setAttribute("cx", x);
                    sc.setAttribute("cy", salesY);
                    sc.setAttribute("r", 6);
                    sc.setAttribute("fill", "#e74c3c");
                    svg.appendChild(sc);

                    // Draw value labels
                    const rl = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    rl.setAttribute("x", x);
                    rl.setAttribute("y", rentalY - 10);
                    rl.setAttribute("font-size", "12");
                    rl.setAttribute("fill", "#4e6ef2");
                    rl.setAttribute("text-anchor", "middle");
                    rl.textContent = `$${p.rental}`;
                    svg.appendChild(rl);

                    const sl = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    sl.setAttribute("x", x);
                    sl.setAttribute("y", salesY - 10);
                    sl.setAttribute("font-size", "12");
                    sl.setAttribute("fill", "#e74c3c");
                    sl.setAttribute("text-anchor", "middle");
                    sl.textContent = `$${p.sales}`;
                    svg.appendChild(sl);

                    points.push([x, rentalY]);
                    salesPoints.push([x, salesY]);
                });

                // Draw animated lines
                function drawLine(pointsArr, color) {
                    const line = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
                    line.setAttribute("points", pointsArr.map(p => p.join(',')).join(' '));
                    line.setAttribute("fill", "none");
                    line.setAttribute("stroke", color);
                    line.setAttribute("stroke-width", "2");
                    line.setAttribute("stroke-linecap", "round");
                    line.setAttribute("stroke-linejoin", "round");
                    line.setAttribute("stroke-dasharray", "1000");
                    line.setAttribute("stroke-dashoffset", "1000");
                    svg.appendChild(line);

                    requestAnimationFrame(() => {
                        line.style.transition = "stroke-dashoffset 1s ease-in-out";
                        line.setAttribute("stroke-dashoffset", "0");
                    });
                }

                drawLine(points, "#4e6ef2");
                drawLine(salesPoints, "#e74c3c");
            }

            // Initial chart
            updateChart(properties[0].id);

            propertySelect.addEventListener('change', function () {
                updateChart(this.value);
            });
        });
});
</script>

<style>
.tpg-chart-container {
    position: relative;
    height: 250px;
    background: #fafbfc;
    border-radius: 8px;
    padding: 30px 20px 50px 50px;
}

/* Y-axis */
.tpg-y-axis {
    position: absolute;
    top: 30px;
    bottom: 50px;
    left: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 500;
    padding-right: 5px;
}

/* SVG Line Chart */
.tpg-line-chart {
    width: 100%;
    height: 100%;
}

/* Chart legend inside chart */
.tpg-chart-legend {
    position: absolute;
    bottom: 10px;
    right: 10px;
    display: flex;
    gap: 10px;
    font-size: 12px;
    font-weight: 500;
    background: rgba(255,255,255,0.8);
    padding: 5px 10px;
    border-radius: 6px;
}
</style>


<style>
/* ===== GENERAL DASHBOARD STYLING ===== */
.dashboard-section { padding: 20px; background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow-x: auto; }
.dashboard-top-right { padding: 16px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow-x: auto; height: 100%; }

/* ===== MESSAGE REALTOR BOX ===== */
.cld-box { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-top: 20px; display: flex; flex-direction: column; overflow: hidden; transition: all 0.3s ease; }
.cld-box-header { background: #3578c6; color: #fff; padding: 12px 16px; font-size: 20px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.2); border-top-left-radius: 12px; border-top-right-radius: 12px; }
.cld-send-btn { background: transparent; border: 2px solid #fff; color: #fff; font-size: 14px; padding: 6px 14px; border-radius: 20px; cursor: pointer; transition: all 0.3s ease; }
.cld-send-btn:hover { background: #fff; color: #3578c6; }
.cld-box-body { padding: 20px; }
.cld-textarea { width: 100%; min-height: 120px; resize: vertical; padding: 10px; font-size: 14px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
.cld-textarea:focus { border-color: #3578c6; box-shadow: 0 0 5px rgba(53,120,198,0.3); }

/* ===== TRACKING PROPERTY CHART ===== */
.tpg-dashboard-container { background: #ffffff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.tpg-tracking-section { position: relative; }
.tpg-tracking-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
.tpg-section-title { margin: 0; font-size: 1.5rem!important; font-weight: 600; color: #2c3e50; }
.tpg-tracking-summary { display: flex; align-items: center; gap: 15px; background: #f8fafd; padding: 10px 15px; border-radius: 8px; }
.tpg-rental { font-size: 1.2rem; font-weight: 700; color: #3578c6; }
.tpg-sales { font-size: 1.2rem; font-weight: 700; color: #e74c3c; }
.tpg-chart-container { position: relative; height: 250px; background: #fafbfc; border-radius: 8px; padding: 30px 20px 50px 50px; display: flex; flex-direction: column; justify-content: space-between; }

/* Y-axis */
.tpg-y-axis { position: absolute; top: 30px; bottom: 50px; left: 0; display: flex; flex-direction: column; justify-content: space-between; font-size: 12px; color: #7f8c8d; font-weight: 500; padding-right: 5px; }

/* X-axis */
.tpg-x-axis { position: relative; bottom: 0; left: 0; right: 0; height: 20px; }

/* Chart Legend */
.tpg-chart-legend { display: flex; gap: 20px; margin-top: 25px; font-size: 13px; font-weight: 500; }

/* SVG Line Chart */
.tpg-line-chart { width: 100%; height: 100%; }
.tpg-line-chart polyline { fill: none; stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
.tpg-line-chart circle { cursor: pointer; transition: transform 0.3s, stroke 0.3s; }
.tpg-line-chart circle:hover { transform: scale(1.5); stroke: #000; stroke-width: 1; }
.tpg-line-chart text { font-family: Arial, sans-serif; font-weight: 600; }

/* Rental & Sales colors */
.tpg-dashboard-container .rental-line { stroke: #4e6ef2; }
.tpg-dashboard-container .rental-circle { fill: #4e6ef2; }
.tpg-dashboard-container .sales-line { stroke: #e74c3c; }
.tpg-dashboard-container .sales-circle { fill: #e74c3c; }

/* ===== NOTES & TABLE ===== */
.active-clients-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.active-clients-table th, .active-clients-table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
table { border-bottom: 1px solid #CCC; }

/* MOBILE RESPONSIVE */
@media screen and (max-width: 480px) {
    .active-clients-table, .active-clients-table thead, .active-clients-table tbody, .active-clients-table th, .active-clients-table tr { display: block; width: 100%; }
    .active-clients-table thead { display: none; }
    .active-clients-table tr { margin-bottom: 15px; border-radius: 8px; background: #f9f9ff; padding: 0 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .active-clients-table td { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
    .active-clients-table td:last-child { border-bottom: none; }
    .active-clients-table td::before { content: attr(data-label); font-weight: 600; color: #333; }
    .dashboard-section { padding: 10px; }
    .tpg-dashboard-container { padding: 10px; }
    .cld-box-body { padding: 10px; }
    .cld-box { margin-bottom: 20px; }
    .dashboard-top-right { padding: 10px; }
}
</style>
