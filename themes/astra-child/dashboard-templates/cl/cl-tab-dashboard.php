<div class="dashboard-top">

    <!-- LEFT SIDE: Tracking Property -->
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
                    <div class="tpg-x-axis"></div>
                </div>

                <div class="tpg-chart-legend" style="display:flex; gap:20px; margin-top:25px;">
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
                <span>Message Realtor</span>
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
            <h1>Notes</h1>
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
    const xAxisDiv = document.querySelector('.tpg-x-axis');
    const yAxisDiv = document.querySelector('.tpg-y-axis');
    const chartWidth = 600;
    const chartHeight = 250;
    const leftPadding = 40;
    const rightPadding = 40; // Right side extra space
    const topPadding = 20;
    const bottomPadding = 40; // Bottom padding for X-axis labels

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

            // Calculate Y-axis max (25% higher than max value)
            const maxRental = Math.max(...properties.map(p => p.rental));
            const maxSales  = Math.max(...properties.map(p => p.sales));
            const yAxisMax  = Math.ceil(Math.max(maxRental, maxSales) * 1.25);

            // Draw Y-axis
            yAxisDiv.innerHTML = '';
            for (let i = 5; i >= 0; i--) {
                const span = document.createElement('span');
                span.textContent = `$${Math.round(yAxisMax * i / 5)}`;
                yAxisDiv.appendChild(span);
            }

            // Draw X-axis
            xAxisDiv.innerHTML = '';
            properties.forEach(p => {
                const span = document.createElement('span');
                span.textContent = p.address.length > 15 ? p.address.substr(0,15)+'…' : p.address;
                span.title = p.address; // full address tooltip
                xAxisDiv.appendChild(span);
            });

            function updateChart(selectedId) {
                svg.innerHTML = ''; // clear svg
                rentalDisplay.textContent = `Avg Rental: $${data.avg_rental}`;
                salesDisplay.textContent  = `Avg Sales: $${data.avg_sales}`;

                const points = [];
                const salesPoints = [];
                const usableWidth = chartWidth - leftPadding - rightPadding;
                const stepX = usableWidth / (properties.length - 1 || 1);

                properties.forEach((p, idx) => {
                    const x = leftPadding + idx * stepX;
                    const rentalY = chartHeight - bottomPadding - (p.rental / yAxisMax) * (chartHeight - bottomPadding - topPadding);
                    const salesY = chartHeight - bottomPadding - (p.sales / yAxisMax) * (chartHeight - bottomPadding - topPadding);

                    points.push([x, rentalY]);
                    salesPoints.push([x, salesY]);

                    // Rental circle
                    const rc = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    rc.setAttribute("cx", x);
                    rc.setAttribute("cy", rentalY);
                    rc.setAttribute("r", 5);
                    rc.setAttribute("fill", "#4e6ef2");
                    rc.classList.add('rental-circle');
                    svg.appendChild(rc);

                    // Rental label
                    const rl = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    rl.setAttribute("x", x);
                    rl.setAttribute("y", rentalY - 10);
                    rl.setAttribute("font-size", "12");
                    rl.setAttribute("fill", "#4e6ef2");
                    rl.setAttribute("text-anchor", "middle"); // center label
                    rl.textContent = `$${p.rental}`;
                    svg.appendChild(rl);

                    // Sales circle
                    const sc = document.createElementNS("http://www.w3.org/2000/svg", "circle");
                    sc.setAttribute("cx", x);
                    sc.setAttribute("cy", salesY);
                    sc.setAttribute("r", 5);
                    sc.setAttribute("fill", "#e74c3c");
                    sc.classList.add('sales-circle');
                    svg.appendChild(sc);

                    // Sales label
                    const sl = document.createElementNS("http://www.w3.org/2000/svg", "text");
                    sl.setAttribute("x", x);
                    sl.setAttribute("y", salesY - 10);
                    sl.setAttribute("font-size", "12");
                    sl.setAttribute("fill", "#e74c3c");
                    sl.setAttribute("text-anchor", "middle"); // center label
                    sl.textContent = `$${p.sales}`;
                    svg.appendChild(sl);
                });

                // Draw lines
                const rentalLine = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
                rentalLine.setAttribute("points", points.map(p => p.join(',')).join(' '));
                rentalLine.setAttribute("fill", "none");
                rentalLine.setAttribute("stroke", "#4e6ef2");
                rentalLine.setAttribute("stroke-width", "2");
                rentalLine.setAttribute("stroke-linecap", "round");
                rentalLine.setAttribute("stroke-linejoin", "round");
                svg.appendChild(rentalLine);

                const salesLine = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
                salesLine.setAttribute("points", salesPoints.map(p => p.join(',')).join(' '));
                salesLine.setAttribute("fill", "none");
                salesLine.setAttribute("stroke", "#e74c3c");
                salesLine.setAttribute("stroke-width", "2");
                salesLine.setAttribute("stroke-linecap", "round");
                salesLine.setAttribute("stroke-linejoin", "round");
                svg.appendChild(salesLine);
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
