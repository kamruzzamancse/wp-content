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
                    <div class="tpg-y-axis">
                        <span>250k</span>
                        <span>200k</span>
                        <span>150k</span>
                        <span>100k</span>
                        <span>50k</span>
                    </div>
                    <svg class="tpg-line-chart" viewBox="0 0 600 250" preserveAspectRatio="none">
                        <polyline class="rental-line" points="" />
                        <polyline class="sales-line" points="" />
                    </svg>
                    <div class="tpg-x-axis">
                        <span>1</span>
                        <span>2</span>
                        <span>3</span>
                        <span>4</span>
                        <span>5</span>
                        <span>6</span>
                    </div>
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
    const rentalLine    = document.querySelector('.rental-line');
    const salesLine     = document.querySelector('.sales-line');

    // Fetch chart data for current user
    fetch('<?php echo admin_url("admin-ajax.php?action=get_rentcast_chart_data"); ?>')
        .then(res => res.json())
        .then(data => {
            const keys = Object.keys(data);
            if (!keys.length) {
                propertySelect.innerHTML = '<option value="">No properties assigned</option>';
                return;
            }

            // Populate dropdown
            propertySelect.innerHTML = '';
            keys.forEach(key => {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = data[key].address;
                propertySelect.appendChild(opt);
            });

            function updateChart(propertyKey) {
                const prop = data[propertyKey];
                if (!prop) return;

                const points = [];
                const salesPoints = [];
                for (let i = 0; i < 6; i++) {
                    points.push([i * 100, 250 - (prop.rental / 1000 * 25)]);
                    salesPoints.push([i * 100, 250 - (prop.sales / 1000 * 25)]);
                }

                rentalLine.setAttribute('points', points.map(p => p.join(',')).join(' '));
                salesLine.setAttribute('points', salesPoints.map(p => p.join(',')).join(' '));

                rentalDisplay.textContent = `Avg Rental: $${prop.rental}`;
                salesDisplay.textContent  = `Avg Sales: $${prop.sales}`;
            }

            updateChart(keys[0]);

            propertySelect.addEventListener('change', function () {
                updateChart(this.value);
            });
        });
});
</script>

<style>
/* Message Realtor Box Styles */

.tpg-sales {
    font-size: 1.2rem;
    font-weight: 700;
    color: #e74c3c; /* red to match sales line */
}

.tpg-rental {
    font-size: 1.2rem;
    font-weight: 700;
    color: #3578c6; /* red to match sales line */
}

.cld-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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

/* Responsive Adjustments */
@media (max-width: 1024px) {
    .cld-box {
        margin-top: 15px;
    }
}

@media (max-width: 600px) {
    .cld-box-header {
        font-size: 14px;
        padding: 10px 12px;
    }

    .cld-send-btn {
        font-size: 12px;
        padding: 4px 10px;
    }

    .cld-textarea {
        min-height: 100px;
        font-size: 13px;
    }
}
</style>

<style>
/* Updated Tracking Property Section (Line Chart) */
.tpg-dashboard-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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

.tpg-amount {
    font-size: 1.4rem;
    font-weight: 700;
    color: #2c3e50;
}

.tpg-year {
    background: #e6f0ff;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
    color: #4e6ef2;
    font-weight: 500;
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

/* Y Axis */
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

/* Space between X Axis and Legend */
.tpg-x-axis {
    position: absolute;
    bottom: 30px; /* increased from 0 to 30px */
    left: 40px;
    right: 0;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 500;
    padding-top: 5px;
}

/* Legend Styling */
.tpg-chart-legend {
    display: flex;
    gap: 20px;
    margin-top: 15px;
    position: relative;
    font-size: 13px;
    font-weight: 500;
}

.tpg-chart-legend span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Line Chart SVG */
.tpg-line-chart {
    width: 100%;
    height: 100%;
}

.tpg-line-chart polyline {
    fill: none;
    stroke: #4e6ef2;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.tpg-line-chart circle {
    fill: #4e6ef2;
    cursor: pointer;
    transition: transform 0.3s, fill 0.3s;
}

.tpg-line-chart circle:hover {
    transform: scale(1.2);
    fill: #6c8dfa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .tpg-tracking-header {
        flex-direction: column;
        align-items: flex-start;
    }
    .tpg-tracking-summary {
        width: 100%;
        justify-content: space-between;
    }
    .tpg-chart-container {
        padding: 10px;
    }
}
</style>

<style>
/* General Styling */
.dashboard-section {
  padding: 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
}

/* Add this for the calendar container */
.dashboard-top-right {
  padding: 16px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
  height: 100%;
}

/* Table Styling (Desktop) */
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

/* Mobile Responsive (Card Style) */
@media screen and (max-width: 480px) {
  .active-clients-table,
  .active-clients-table thead,
  .active-clients-table tbody,
  .active-clients-table th,
  .active-clients-table tr {
    display: block;
    width: 100%;
  }

  .active-clients-table thead {
    display: none;
  }

  .active-clients-table tr {
    margin-bottom: 15px;
    border-radius: 8px;
    background: #f9f9ff;
    padding: 0 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }

  .active-clients-table td {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
  }

  .active-clients-table td:last-child {
    border-bottom: none;
  }

  .active-clients-table td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #333;
  }

  .dashboard-section {
    padding: 10px;
   }

   table {
        border-width: 0!important;
    }

    .tpg-dashboard-container {
        padding: 10px;
    }

    .cld-box-body {
        padding: 10px;
    }

    .cld-box {
        margin-bottom: 20px;
    }

    .dashboard-top-right {
        padding: 10px;
    }

}

/* Rental Income */
.tpg-dashboard-container .rental-line {
    stroke: #4e6ef2;
}
.tpg-dashboard-container .rental-circle {
    fill: #4e6ef2;
}

/* Sales Price */
.tpg-dashboard-container .sales-line {
    stroke: #e74c3c;
}
.tpg-dashboard-container .sales-circle {
    fill: #e74c3c;
}
</style>
