<div class="dashboard-top">

    <!-- LEFT SIDE: Tracking Property -->
    <div class="dashboard-top-left">
        <div class="tpg-dashboard-container">
            <div class="tpg-tracking-section">
                <div class="tpg-tracking-header">
                    <h2 class="header-title">📈 Tracking Property</h2>
                    <div class="tpg-tracking-summary">
                        <span class="tpg-rental" id="tpg-avg-rental-price">Avg Rental: $0</span> | 
                        <span class="tpg-sales" id="tpg-avg-sales-price">Avg Sales: $0</span>
                    </div>
                    
                    <!-- Radio Buttons for Chart Type -->
                    <div class="tpg-chart-type-selector" style="margin: 15px 0; display: flex; gap: 20px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="chartType" value="rental" checked style="cursor: pointer;">
                            <span>Rental</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="chartType" value="sale" style="cursor: pointer;">
                            <span>Sale</span>
                        </label>
                    </div>
                    
                    <select id="tpg-property-select">
                        <option value="">Loading...</option>
                    </select>
                </div>

                <div class="tpg-chart-container">
                    <div class="tpg-y-axis"></div>
                    <svg class="tpg-line-chart" viewBox="0 0 600 250" preserveAspectRatio="none"></svg>
                    <div class="tpg-x-axis"></div>
                </div>

                <div class="tpg-chart-legend" id="tpg-chart-legend" style="display:flex; gap:20px; margin-top:25px;">
                    <span style="display:flex; align-items:center; gap:5px;">
                        <span style="width:12px; height:12px; background:#4e6ef2; display:inline-block;"></span> Rental Income
                    </span>
                    <span style="display:flex; align-items:center; gap:5px;">
                        <span style="width:12px; height:12px; background:#e74c3c; display:inline-block;"></span> Sales Price
                    </span>
                </div>
            </div>
        </div>

        <!-- Message Realtor Box -->
        <div class="cld-box cld-message-box">
            <div class="cld-box-header">
                <span>📩 Message Realtor</span>
                <button class="cld-send-btn">Send</button>
            </div>
            <div class="cld-box-body">
                <textarea class="cld-textarea" placeholder="Type your message here"></textarea>
            </div>
        </div>
    </div>

    <!-- RIGHT SIDE: Calendar + Notes -->
    <div class="dashboard-top-right">
        <?php
        $current_user = wp_get_current_user();
        $user_email   = $current_user->user_email;

        if ($user_email) {
            global $wpdb;
            $calendar_id = $wpdb->get_var($wpdb->prepare("
                SELECT ID 
                FROM $wpdb->posts 
                WHERE post_type = 'calendar' 
                  AND post_status = 'publish'
                  AND post_title = %s
                LIMIT 1
            ", $user_email));

            if ($calendar_id) {
                echo do_shortcode('[calendar id="' . intval($calendar_id) . '"]');
            } else {
                echo '<p>No calendar found for your account.</p>';
            }
        } else {
            echo '<p>Please login to see your calendar.</p>';
        }
        ?>

        <!-- Notes Header -->
        <div class="notes-header">
            <h1>📝 Notes</h1>
            <button class="add-note-btn">+</button>
        </div>

        <!-- Sticky Notes Container -->
        <div class="sticky-notes-container"></div>
    </div>
</div>

<!-- ======== JS ======== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rentalDisplay = document.getElementById('tpg-avg-rental-price');
    const salesDisplay  = document.getElementById('tpg-avg-sales-price');
    const propertySelect = document.getElementById('tpg-property-select');
    const svg = document.querySelector('.tpg-line-chart');
    const yAxisDiv = document.querySelector('.tpg-y-axis');
    const xAxisDiv = document.querySelector('.tpg-x-axis');
    const chartLegend = document.getElementById('tpg-chart-legend');
    const chartWidth = 600;
    const chartHeight = 250;

    let chartData = null;
    let currentChartType = 'rental';

    // Handle chart type change
    document.querySelectorAll('input[name="chartType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            currentChartType = this.value;
            if (chartData && propertySelect.value) updateChart(propertySelect.value);
        });
    });

    function formatFullCurrency(value) {
        if (value === null || value === undefined) return '$0';
        return '$' + value.toLocaleString();
    }

    function drawYAxis(max) {
        yAxisDiv.innerHTML = '';
        if (!max || max <= 0) max = 10000;
        const ticks = 5;
        for (let i = ticks; i >= 0; i--) {
            const span = document.createElement('span');
            span.textContent = formatFullCurrency(Math.round(max * i / ticks));
            yAxisDiv.appendChild(span);
        }
    }

    function drawXAxis(labels) {
        xAxisDiv.innerHTML = '';
        labels.forEach(label => {
            const span = document.createElement('span');
            span.textContent = label;
            xAxisDiv.appendChild(span);
        });
    }

    function updateLegendVisibility() {
        const rentalLegend = chartLegend.querySelector('span:nth-child(1)');
        const salesLegend = chartLegend.querySelector('span:nth-child(2)');
        rentalLegend.style.display = (currentChartType === 'rental' || currentChartType === 'both') ? 'flex' : 'none';
        salesLegend.style.display = (currentChartType === 'sale' || currentChartType === 'both') ? 'flex' : 'none';
    }

    function drawAnimatedLine(points, color, width) {
        const line = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
        line.setAttribute("points", points.map(p => p.join(',')).join(' '));
        line.setAttribute("fill", "none");
        line.setAttribute("stroke", color);
        line.setAttribute("stroke-width", width);
        line.setAttribute("stroke-linecap", "round");
        line.setAttribute("stroke-linejoin", "round");
        line.setAttribute("stroke-dasharray", "1000");
        line.setAttribute("stroke-dashoffset", "1000");
        svg.appendChild(line);

        setTimeout(() => {
            line.style.transition = "stroke-dashoffset 1s ease-in-out";
            line.setAttribute("stroke-dashoffset", "0");
        }, 50);
    }

    function renderRentalTrendChart(property) {
        const data = property.monthly_rental_data || property.historical_rental_prices;
        if (!data || Object.keys(data).length === 0) return;

        svg.innerHTML = '';
        const leftPadding = 60, rightPadding = 30, topPadding = 30, bottomPadding = 60;
        const usableWidth = chartWidth - leftPadding - rightPadding;
        const usableHeight = chartHeight - topPadding - bottomPadding;

        const dates = Object.keys(data).sort();
        const values = dates.map(d => data[d].price || 0);

        const maxValue = Math.max(...values);
        const yAxisMax = maxValue * 1.2;

        drawYAxis(yAxisMax);
        drawXAxis(dates.map(d => {
            const dt = new Date(d);
            if (isNaN(dt)) return d;
            const day   = dt.getDate().toString().padStart(2,'0');
            const month = (dt.getMonth()+1).toString().padStart(2,'0');
            const year  = dt.getFullYear().toString().slice(-2);
            return `${month}-${day}-${year}`;
        }));

        const points = [];
        dates.forEach((date, idx) => {
            const value = values[idx];
            if (value === null || value === undefined || value <= 0) return;

            const x = leftPadding + (idx * usableWidth / (dates.length - 1));
            const y = topPadding + ((yAxisMax - value) / yAxisMax) * usableHeight;
            points.push([x, y]);

            const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            circle.setAttribute("cx", x);
            circle.setAttribute("cy", y);
            circle.setAttribute("r", 4);
            circle.setAttribute("fill", "#4e6ef2");
            svg.appendChild(circle);

            const label = document.createElementNS("http://www.w3.org/2000/svg","text");
            label.setAttribute("x", x);
            label.setAttribute("y", y - 10);
            label.setAttribute("font-size", "9");
            label.setAttribute("fill", "#4e6ef2");
            label.setAttribute("text-anchor", "middle");
            label.textContent = formatFullCurrency(value);
            svg.appendChild(label);
        });

        if (points.length > 1) drawAnimatedLine(points, "#4e6ef2", 2);

        const title = document.createElementNS("http://www.w3.org/2000/svg","text");
        title.setAttribute("x", chartWidth / 2);
        title.setAttribute("y", 15);
        title.setAttribute("font-size","14");
        title.setAttribute("fill","#333");
        title.setAttribute("text-anchor","middle");
        title.setAttribute("font-weight","bold");
        title.textContent = `Rental Trend - ${property.address}`;
        svg.appendChild(title);

        updateLegendVisibility();

        rentalDisplay.textContent = `Avg Rental: ${formatFullCurrency(chartData.avg_rental)}`;
        salesDisplay.textContent  = `Avg Sales: ${formatFullCurrency(chartData.avg_sales)}`;
    }

    function updateChart(selectedValue) {
        if (!chartData || !chartData.properties) return;

        const properties = chartData.properties;
        let propsToShow = selectedValue === 'all' ? properties : properties.filter(p => p.id == selectedValue);
        if (!propsToShow.length) return;

        if (selectedValue !== 'all' && currentChartType === 'rental' && 
            (propsToShow[0].monthly_rental_data || propsToShow[0].historical_rental_prices)) {
            renderRentalTrendChart(propsToShow[0]);
            return;
        }

        svg.innerHTML = '';
        const yAxisMax = Math.max(...propsToShow.map(p => {
            const value = currentChartType==='rental'?p.rental:p.sales;
            return value > 0 ? value : 0;
        })) * 1.25 || 10000;

        drawYAxis(yAxisMax);
        drawXAxis(propsToShow.map((_, idx) => `P${idx+1}`));
        updateLegendVisibility();

        const leftPadding = 50, rightPadding=50, topPadding=30, bottomPadding=50;
        const usableWidth = chartWidth - leftPadding - rightPadding;
        const usableHeight = chartHeight - topPadding - bottomPadding;
        const stepX = propsToShow.length>1 ? usableWidth/(propsToShow.length-1) : usableWidth/2;

        const points = [];
        propsToShow.forEach((p, idx) => {
            const value = currentChartType==='rental'?p.rental:p.sales;
            if (value === null || value === undefined || value <= 0) return;

            const x = leftPadding + idx*stepX;
            const y = topPadding + ((yAxisMax - value) / yAxisMax)*usableHeight;
            points.push([x,y]);

            const circle = document.createElementNS("http://www.w3.org/2000/svg","circle");
            circle.setAttribute("cx", x);
            circle.setAttribute("cy", y);
            circle.setAttribute("r", 6);
            circle.setAttribute("fill", currentChartType==='rental'?'#4e6ef2':'#e74c3c');
            svg.appendChild(circle);

            const label = document.createElementNS("http://www.w3.org/2000/svg","text");
            label.setAttribute("x", x);
            label.setAttribute("y", y - 12);
            label.setAttribute("font-size","10");
            label.setAttribute("fill", currentChartType==='rental'?'#4e6ef2':'#e74c3c');
            label.setAttribute("text-anchor","middle");
            label.textContent = formatFullCurrency(value);
            svg.appendChild(label);
        });

        if (points.length>1) drawAnimatedLine(points,currentChartType==='rental'?'#4e6ef2':'#e74c3c',2);

        const title = document.createElementNS("http://www.w3.org/2000/svg","text");
        title.setAttribute("x", chartWidth/2);
        title.setAttribute("y",15);
        title.setAttribute("font-size","14");
        title.setAttribute("fill","#333");
        title.setAttribute("text-anchor","middle");
        title.setAttribute("font-weight","bold");
        title.textContent = currentChartType==='rental'?'Rental Income':'Sales Price';
        if (selectedValue!=='all') title.textContent += ` - ${propsToShow[0].address}`;
        svg.appendChild(title);

        rentalDisplay.textContent = `Avg Rental: ${formatFullCurrency(chartData.avg_rental)}`;
        salesDisplay.textContent = `Avg Sales: ${formatFullCurrency(chartData.avg_sales)}`;
    }

    fetch('<?php echo admin_url("admin-ajax.php?action=get_rentcast_chart_data"); ?>')
        .then(res => res.json())
        .then(data => {
            chartData = data;
            let properties = data.properties || [];
            if (!properties.length) {
                propertySelect.innerHTML = '<option value="">No properties assigned</option>';
                return;
            }

            // Descending order by address
            properties.sort((a, b) => (a.address < b.address ? 1 : a.address > b.address ? -1 : 0));

            propertySelect.innerHTML = '<option value="">Select a property</option>';
            properties.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.address;
                opt.setAttribute('title',`Rental: $${p.rental.toLocaleString()} | Sales: $${p.sales.toLocaleString()}`);
                propertySelect.appendChild(opt);
            });

            propertySelect.value = properties[0].id;
            updateChart(properties[0].id);
            propertySelect.addEventListener('change', function(){ if(this.value) updateChart(this.value); });
        })
        .catch(err => {
            console.error(err);
            propertySelect.innerHTML = '<option value="">Error loading properties</option>';
            svg.innerHTML = `<text x="${chartWidth/2}" y="${chartHeight/2}" font-size="14" fill="#999" text-anchor="middle">Error loading chart data. Please try again.</text>`;
        });
});
</script>


<style>
/* ===== GENERAL DASHBOARD STYLING ===== */

.dashboard-section {
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow-x: auto;
}

.dashboard-top-right {
    padding: 16px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow-x: auto;
    height: 100%;
}

/* ===== MESSAGE REALTOR BOX ===== */
.cld-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.3s ease;
}

.cld-box-header {
    background: #3578c6;
    color: #fff;
    padding: 12px 16px;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

.cld-send-btn {
    background: transparent;
    border: 2px solid #fff;
    color: #fff;
    font-size: 14px;
    padding: 6px 14px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cld-send-btn:hover {
    background: #fff;
    color: #3578c6;
}

.cld-box-body {
    padding: 20px;
}

.cld-textarea {
    width: 100%;
    min-height: 120px;
    resize: vertical;
    padding: 10px;
    font-size: 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    outline: none;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.cld-textarea:focus {
    border-color: #3578c6;
    box-shadow: 0 0 5px rgba(53,120,198,0.3);
}

/* ===== TRACKING PROPERTY CHART ===== */
.tpg-dashboard-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.tpg-tracking-section {
    position: relative;
}

.tpg-tracking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.tpg-section-title {
    margin: 0;
    font-size: 1.5rem!important;
    font-weight: 600;
    color: #2c3e50;
}

.tpg-tracking-summary {
    display: flex;
    align-items: center;
    gap: 15px;
    background: #f8fafd;
    padding: 10px 15px;
    border-radius: 8px;
}

.tpg-rental {
    font-size: 1.2rem;
    font-weight: 700;
    color: #3578c6;
}

.tpg-sales {
    font-size: 1.2rem;
    font-weight: 700;
    color: #e74c3c;
}

.tpg-chart-container {
    position: relative;
    height: 250px;
    background: #fafbfc;
    border-radius: 8px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Y-axis */
.tpg-y-axis {
    position: absolute;
    top: 15px;
    bottom: 30px;
    left: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 500;
    padding-right: 5px;
}

.tpg-y-axis span {
    line-height: 1.4;
}

/* Chart Legend */
.tpg-chart-legend {
    display: flex;
    gap: 20px;
    margin-top: 15px;
    font-size: 13px;
    font-weight: 500;
}

/* SVG Line Chart */
.tpg-line-chart {
    width: 100%;
    height: 100%;
    font-size: 14px;
    font-weight: bold;
    fill: #333;
    text-anchor: middle;
}

.tpg-line-chart polyline {
    fill: none;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.tpg-line-chart circle {
    cursor: pointer;
    transition: transform 0.3s, stroke 0.3s;
}

.tpg-line-chart circle:hover {
    transform: scale(1.5);
    stroke: #000;
    stroke-width: 1;
}

.tpg-line-chart text {
    font-family: Arial, sans-serif;
    font-weight: 600;
    dominant-baseline: hanging; /* ensures the text starts at the y position */
}

/* Rental & Sales colors */
.tpg-dashboard-container .rental-line { stroke: #4e6ef2; }
.tpg-dashboard-container .rental-circle { fill: #4e6ef2; }
.tpg-dashboard-container .sales-line { stroke: #e74c3c; }
.tpg-dashboard-container .sales-circle { fill: #e74c3c; }

/* ===== NOTES & TABLE ===== */
.active-clients-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.active-clients-table th,
.active-clients-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

table {
    border-bottom: 1px solid #CCC;
}

/* MOBILE RESPONSIVE */
@media screen and (max-width: 480px) {
    .active-clients-table,
    .active-clients-table thead,
    .active-clients-table tbody,
    .active-clients-table th,
    .active-clients-table tr {
        display: block;
        width: 100%;
    }

    .active-clients-table thead { display: none; }

    .active-clients-table tr {
        margin-bottom: 15px;
        border-radius: 8px;
        background: #f9f9ff;
        padding: 0 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .active-clients-table td {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .active-clients-table td:last-child { border-bottom: none; }

    .active-clients-table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #333;
    }

    .dashboard-section { padding: 10px; }
    .tpg-dashboard-container { padding: 10px; }
    .cld-box-body { padding: 10px; }
    .cld-box { margin-bottom: 20px; }
    .dashboard-top-right { padding: 10px; }
    .tpg-x-axis span { max-width: 60px; }
}

/* Show after X-axis labels */
.tpg-x-axis {
    position: absolute;
    bottom: -20px;
    left: 40px;
    right: 0;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 500;
}

.tpg-x-axis span {
    display: inline-block;
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: top;
    text-align: center;
}
</style>
