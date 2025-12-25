<div class="dashboard-top">

    <!-- LEFT SIDE: Tracking Property -->
    <div class="dashboard-top-left">
        <div class="tpg-dashboard-container shadow-premium">
            <div class="tpg-tracking-section">
                <div class="tpg-tracking-header">
                    <h2 class="tpg-section-title">📈 Tracking Property</h2>
                    
                    <div class="tpg-tracking-summary">
                        <span class="tpg-rental" id="tpg-avg-rental-price">Avg Rental: $0</span>
                        <span style="color: #cbd5e0;">|</span>
                        <span class="tpg-sales" id="tpg-avg-sales-price">Avg Sales: $0</span>
                    </div>
                    
                    <div class="tpg-chart-type-selector">
                        <label class="radio-chip">
                            <input type="radio" name="chartType" value="rental" checked>
                            <span class="chip-label">Rental</span>
                        </label>
                        <label class="radio-chip">
                            <input type="radio" name="chartType" value="sale">
                            <span class="chip-label">Sale</span>
                        </label>
                    </div>
                    
                    <select id="tpg-property-select" class="dashboard-dropdown">
                        <option value="">Loading Properties...</option>
                    </select>
                </div>

                <div id="tpg-dynamic-title-outer" class="chart-title-outer"></div>

                <div class="tpg-chart-container-modern">
                    <div class="tpg-y-axis" id="tpg-y-labels"></div>
                    
                    <div style="position: relative; flex-grow: 1; height: 250px; margin-left: 65px;">
                        <svg class="tpg-line-chart" viewBox="0 0 600 250" preserveAspectRatio="none" style="overflow: visible;"></svg>
                        
                        <div class="tpg-x-axis" id="tpg-x-labels"></div>
                    </div>
                </div>

                <div class="tpg-chart-legend">
                    <div class="legend-item">
                        <span class="color-box rental-box"></span> Rental Income
                    </div>
                    <div class="legend-item">
                        <span class="color-box sales-box"></span> Sales Price
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Realtor Box -->
        <!-- <div class="cld-box cld-message-box">
            <div class="cld-box-header">
                <span>📩 Message Realtor</span>
                <button class="cld-send-btn">Send</button>
            </div>
            <div class="cld-box-body">
                <textarea class="cld-textarea" placeholder="Type your message here"></textarea>
            </div>
        </div> -->

        <!-- ===== NOTE HEADER BOX ===== -->
        <div class="cld-box cld-note-header-box">
            <div class="cld-box-header">
                <span>📝 Note Header</span>
                <button type="button" class="cld-send-btn" id="add-note-header-btn">
                    Add Note Header
                </button>
            </div>
            <div class="cld-box-body">
                <ul id="note-header-list">
                    <li class="empty">No note headers found</li>
                </ul>
            </div>
        </div>

        <!-- ===== NOTE HEADER MODAL ===== -->
        <div id="noteHeaderModal" class="note-modal">
            <div class="note-modal-content">
                <span class="note-modal-close" data-modal="noteHeaderModal">&times;</span>
                <h3 id="note-header-modal-title">Add Note Header</h3>
                <input type="hidden" id="note_id">
                <label for="note_header_input">Note Header</label>
                <input type="text" id="note_header_input" placeholder="Note Header">
                <button type="button" id="save_note_header" class="cld-send-btn">Save</button>
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
        <!-- <div class="notes-header">
            <h1>📝 Notes</h1>
            <button class="add-note-btn">+</button>
        </div> -->

        <!-- Sticky Notes Container -->
        <!-- <div class="sticky-notes-container"></div> -->

        <!-- ===== NOTES BOX ===== -->
        <div class="cld-box cld-notes-box">
            <div class="cld-box-header">
                <span>📝 Notes</span>
                <button type="button" class="cld-send-btn" id="add-note-btn">Add Note</button>
            </div>
            <div class="cld-box-body">
                <ul id="notes-list" class="notes-preview-list">
                    <li class="empty">No notes found</li>
                </ul>
                <button id="view-all-notes-btn" class="cld-send-btn" style="display:none; margin-top:10px;">View All Notes</button>
            </div>
        </div>

        <!-- ===== NOTE MODAL (Add/Edit Note) ===== -->
        <div id="noteModal" class="note-modal">
            <div class="note-modal-content">
                <span class="note-modal-close" data-modal="noteModal">&times;</span>
                <h3 id="note-modal-title">Add Note</h3>
                <input type="hidden" id="note_row_id">
                <label for="note_header_select">Note Header</label>
                <select id="note_header_select">
                    <option value="">Select Note Header</option>
                </select>
                <label for="note_text">Note</label>
                <textarea id="note_text" placeholder="Write your note..." rows="5"></textarea>
                <button type="button" id="save_note" class="cld-send-btn">Save</button>
            </div>
        </div>

        <!-- ===== VIEW ALL NOTES MODAL ===== -->
        <div id="allNotesModal" class="note-modal">
            <div class="note-modal-content" style="width:500px; max-height:70vh; overflow-y:auto;">
                <span class="note-modal-close" data-modal="allNotesModal">&times;</span>
                <h3>All Notes</h3>
                <ul id="all-notes-list">
                    <!-- All notes will be appended here -->
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- ======== JS ======== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rentalDisplay = document.getElementById('tpg-avg-rental-price');
    const salesDisplay = document.getElementById('tpg-avg-sales-price');
    const titleOuter = document.getElementById('tpg-dynamic-title-outer');
    const xAxisLabels = document.getElementById('tpg-x-labels');
    const yAxisLabels = document.getElementById('tpg-y-labels');
    const svg = document.querySelector('.tpg-line-chart');
    const propertySelect = document.getElementById('tpg-property-select');

    let chartData = null;
    let currentMode = 'rental';

    function drawChart(propId) {
        if (!chartData) return;
        const prop = chartData.properties.find(p => p.id == propId);
        if (!prop) return;

        rentalDisplay.textContent = `Avg Rental: $${Number(chartData.avg_rental || 0).toLocaleString()}`;
        salesDisplay.textContent = `Avg Sales: $${Number(chartData.avg_sales || 0).toLocaleString()}`;
        titleOuter.textContent = (currentMode === 'rental' ? 'Rental Trend' : 'Sales Price') + ' - ' + prop.address;

        svg.innerHTML = '';
        xAxisLabels.innerHTML = '';
        yAxisLabels.innerHTML = '';

        const width = 600, height = 250;
        // Overlap prevent korar jonno left margin 70px kora hoyeche
        const margin = { top: 60, bottom: 20, left: 70, right: 30 };
        
        let labels = [], values = [];
        if (currentMode === 'rental') {
            const history = prop.monthly_rental_data || {};
            labels = Object.keys(history).sort();
            values = labels.map(d => history[d].price || 0);
        } else {
            labels = ['P1']; 
            values = [prop.sales || 0];
        }

        const maxVal = Math.max(...values, 1000) * 1.3;

        // Y-Axis Labels logic
        for (let i = 5; i >= 0; i--) {
            const span = document.createElement('span');
            span.textContent = '$' + Math.round(maxVal * i / 5).toLocaleString();
            yAxisLabels.appendChild(span);
        }

        const points = [];
        const themeColor = currentMode === 'rental' ? '#4e6ef2' : '#e74c3c';

        labels.forEach((label, idx) => {
            const val = values[idx];
            
            // Value gulo jeno label er opore na jay sheijonno drawing area width adjust kora hoyeche
            const chartAreaWidth = width - margin.left - margin.right;
            const x = labels.length > 1 
                ? margin.left + (idx * (chartAreaWidth / (labels.length - 1))) 
                : margin.left + (chartAreaWidth / 2); // Single point (Sales) thakle center position

            const y = ((maxVal - val) / maxVal) * (height - margin.top - margin.bottom) + margin.top;
            points.push([x, y]);

            // Value Text (Exact center alignment)
            const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
            text.setAttribute("x", x);
            text.setAttribute("y", y - 15);
            text.setAttribute("text-anchor", "middle");
            text.setAttribute("fill", themeColor);
            text.setAttribute("style", "font-size: 13px; font-weight: bold;");
            text.textContent = '$' + Number(val).toLocaleString();
            svg.appendChild(text);

            // Pointer Dot
            const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            circle.setAttribute("cx", x); circle.setAttribute("cy", y); circle.setAttribute("r", 5);
            circle.setAttribute("fill", themeColor);
            svg.appendChild(circle);

            // X-Axis Label (P1 alignment fix)
            const xSpan = document.createElement('span');
            xSpan.textContent = label;
            xSpan.style.position = "absolute";
            // SVG x coordinate ke container er percentage e convert kora hoyeche
            xSpan.style.left = `${(x / width) * 100}%`;
            xSpan.style.transform = "translateX(-50%)"; // Thik center alignment
            xSpan.style.whiteSpace = "nowrap";
            xAxisLabels.appendChild(xSpan);
        });

        if (points.length > 1) {
            const polyline = document.createElementNS("http://www.w3.org/2000/svg", "polyline");
            polyline.setAttribute("points", points.map(p => p.join(',')).join(' '));
            polyline.setAttribute("stroke", themeColor);
            polyline.setAttribute("stroke-width", "3");
            polyline.setAttribute("fill", "none");
            svg.appendChild(polyline);
        }
    }

    document.querySelectorAll('input[name="chartType"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            currentMode = e.target.value;
            drawChart(propertySelect.value);
        });
    });

    propertySelect.addEventListener('change', (e) => drawChart(e.target.value));

    fetch('<?php echo admin_url("admin-ajax.php?action=get_rentcast_chart_data"); ?>')
        .then(res => res.json())
        .then(data => {
            chartData = data;
            if(data.properties && data.properties.length > 0) {
                propertySelect.innerHTML = data.properties.map(p => `<option value="${p.id}">${p.address}</option>`).join('');
                drawChart(data.properties[0].id);
            }
        });
});
</script>

<style>
/* ============================================================
   1. TRACKING PROPERTY CHART (GRAPH) STYLING 
   ============================================================ */

.tpg-dashboard-container.shadow-premium {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #f0f2f5;
}

.chart-title-outer {
    text-align: center;
    margin-bottom: 20px;
    font-size: 16px;
    color: #2c3e50;
    padding: 8px;
    background: #f8fafd;
    border-radius: 8px;
}

.tpg-tracking-summary {
    display: flex;
    gap: 10px;
    font-weight: 600;
    background: #fff;
    padding: 8px 15px;
    border: 1px solid #eee;
    border-radius: 30px;
}

.tpg-chart-container-modern {
    position: relative;
    height: 250px;
    margin-top: 10px;
    background: linear-gradient(to bottom, #ffffff, #fcfdfe);
    padding-left: 10px;
}

.tpg-y-axis {
    position: absolute;
    top: 20px;
    bottom: 55px;
    left: 10px;
    width: 55px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    font-size: 11px;
    color: #7f8c8d;
    text-align: right;
}

.tpg-y-axis span {
    line-height: 1.4;
}

.tpg-x-axis {
    position: absolute;
    bottom: 10px;
    left: 70px;
    right: 20px;
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    color: #7f8c8d;
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
    padding: 20px 15px 40px 70px;
}

.tpg-chart-title {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    min-height: 20px;
}

.tpg-chart-legend {
    display: flex;
    gap: 20px;
    margin-top: 25px;
    font-size: 13px;
    padding-left: 10px;
}

.legend-item { display: flex; align-items: center; gap: 8px; }
.color-box { width: 12px; height: 12px; display: inline-block; border-radius: 2px; }
.rental-box { background: #4e6ef2; }
.sales-box { background: #e74c3c; }

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
    font-weight: bold;
}

.tpg-dashboard-container .rental-line { stroke: #4e6ef2; }
.tpg-dashboard-container .rental-circle { fill: #4e6ef2; }
.tpg-dashboard-container .sales-line { stroke: #e74c3c; }
.tpg-dashboard-container .sales-circle { fill: #e74c3c; }

.radio-chip input { display: none; }
.chip-label {
    padding: 6px 16px;
    background: #f1f5f9;
    border-radius: 20px;
    cursor: pointer;
    font-size: 13px;
    transition: 0.3s;
}
.radio-chip input:checked + .chip-label {
    background: #4e6ef2;
    color: #fff;
}
.tpg-chart-type-selector {
    display: flex;
    gap: 10px;
}

/* ============================================================
   2. GENERAL DASHBOARD & LAYOUT
   ============================================================ */

* {
    box-sizing: border-box;
}

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

/* ============================================================
   3. MESSAGE REALTOR BOX (CLD BOX)
   ============================================================ */

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

/* ============================================================
   4. BUTTONS
   ============================================================ */

.cld-send-btn {
    display: inline-block;
    padding: 10px;
    background: #4e6ef2;
    color: #fff !important;
    border: 1px solid #FFF;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
}

.cld-send-btn:hover {
    background: #3b54c1;
}

.cld-send-btn.outlined {
    background: transparent;
    border: 2px solid #fff;
    padding: 6px 14px;
    color: #fff !important;
}

.cld-send-btn.outlined:hover {
    background: rgba(255, 255, 255, 0.1);
}

#save_note.cld-send-btn {
    width: 100%;
}

#view-all-notes-btn {
    display: none;
    margin-top: 10px;
}

/* ============================================================
   5. NOTES & HEADER LIST STYLING
   ============================================================ */

#note-header-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

#note-header-list li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    font-size: 15px;
    background: #fff;
    transition: background 0.2s ease;
}

#note-header-list li:hover {
    background: #f9f9f9;
}

.note-header-text {
    flex: 1;
    font-weight: 500;
    color: #333;
}

.edit-header, .delete-header {
    margin-left: 10px;
    font-size: 14px;
    cursor: pointer;
    user-select: none;
}

.edit-header:hover { color: #3578c6; }
.delete-header:hover { color: #e74c3c; }

#notes-list, .notes-preview-list, #all-notes-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

#notes-list li, .notes-preview-list li, #all-notes-list li {
    padding: 10px 12px;
    border-bottom: 1px solid #eee;
    border-radius: 5px;
    margin-bottom: 5px;
    transition: background 0.2s;
}

.note-header-title {
    font-weight: 600;
    margin-bottom: 5px;
    font-size: 15px;
    color: #333;
}

.note-text {
    font-size: 14px;
    color: #555;
    margin-bottom: 5px;
}

.note-meta {
    font-size: 12px;
    color: #999;
}

.note-actions { margin-top: 6px; }
.note-actions span {
    cursor: pointer;
    margin-right: 10px;
    font-size: 13px;
    color: #3578c6;
}

.note-actions span.delete-note { color: #e74c3c; }
.notes-preview-list { max-height: 600px; overflow-y: auto; }

/* ============================================================
   6. MODALS
   ============================================================ */

.note-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.4);
    align-items: center;
    justify-content: center;
}

.note-modal.show { display: flex; }

.note-modal-content {
    background: #fff;
    padding: 20px 25px;
    width: 400px;
    border-radius: 10px;
    position: relative;
    box-shadow: 0 5px 25px rgba(0,0,0,0.15);
}

#allNotesModal .note-modal-content {
    width: 500px;
    max-height: 70vh;
    overflow-y: auto;
}

.note-modal-content h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    font-weight: 600;
    color: #222;
}

.note-modal-content label { display: block; margin-bottom: 5px; font-size: 14px; }

.note-modal-content input,
.note-modal-content select,
.note-modal-content textarea {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
}

.note-modal-close {
    position: absolute;
    top: 10px;
    right: 12px;
    font-size: 22px;
    cursor: pointer;
    color: #555;
    transition: color 0.2s;
}

.note-modal-close:hover { color: #000; }

/* ============================================================
   7. TABLES
   ============================================================ */

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

table { border-bottom: 1px solid #CCC; }

/* ============================================================
   8. MOBILE RESPONSIVE
   ============================================================ */

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
</style>
