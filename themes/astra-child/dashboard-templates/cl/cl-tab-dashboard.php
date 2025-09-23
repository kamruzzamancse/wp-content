<div class="dashboard-top">
    <!-- LEFT SIDE -->
    <div class="dashboard-top-left">
        <div class="tpg-dashboard-container">
            <div class="tpg-tracking-section">

                <!-- Header with Dropdown -->
                <div class="tpg-tracking-header">
                    <h1 class="tpg-section-title">Tracking Property</h1>

                    <div class="tpg-tracking-summary">
                        <span class="tpg-rental" id="tpg-avg-rental-price">Avg Rental: $5.34K</span> | 
                        <span class="tpg-sales" id="tpg-avg-sales-price">Avg Sales: $250K</span>
                    </div>

                    <select id="tpg-property-select">
                        <option value="property1">Property 1</option>
                        <option value="property2">Property 2</option>
                        <option value="property3">Property 3</option>
                    </select>
                </div>

                <div class="tpg-chart-container">

                    <!-- Y Axis -->
                    <div class="tpg-y-axis">
                        <span>250k</span>
                        <span>200k</span>
                        <span>150k</span>
                        <span>100k</span>
                        <span>50k</span>
                    </div>

                    <!-- Line Chart -->
                    <svg class="tpg-line-chart" viewBox="0 0 600 250" preserveAspectRatio="none">
                        <!-- Rental Income Line (Blue) -->
                        <polyline class="rental-line" points="0,210 100,180 200,150 300,120 400,80 500,40" />
                        <circle class="rental-circle" cx="0" cy="210" r="5" data-value="$2.10k"></circle>
                        <circle class="rental-circle" cx="100" cy="180" r="5" data-value="$3.20k"></circle>
                        <circle class="rental-circle" cx="200" cy="150" r="5" data-value="$4.80k"></circle>
                        <circle class="rental-circle" cx="300" cy="120" r="5" data-value="$6.20k"></circle>
                        <circle class="rental-circle" cx="400" cy="80" r="5" data-value="$7.50k"></circle>
                        <circle class="rental-circle" cx="500" cy="40" r="5" data-value="$8.24k"></circle>

                        <!-- Sales Price Line (Red) -->
                        <polyline class="sales-line" points="0,200 100,190 200,170 300,140 400,110 500,70" />
                        <circle class="sales-circle" cx="0" cy="200" r="5" data-value="$180k"></circle>
                        <circle class="sales-circle" cx="100" cy="190" r="5" data-value="$185k"></circle>
                        <circle class="sales-circle" cx="200" cy="170" r="5" data-value="$190k"></circle>
                        <circle class="sales-circle" cx="300" cy="140" r="5" data-value="$200k"></circle>
                        <circle class="sales-circle" cx="400" cy="110" r="5" data-value="$220k"></circle>
                        <circle class="sales-circle" cx="500" cy="70" r="5" data-value="$250k"></circle>
                    </svg>

                    <!-- X Axis -->
                    <div class="tpg-x-axis">
                        <span>10:30 AM</span>
                        <span>11:30 AM</span>
                        <span>12:30 PM</span>
                        <span>1:30 PM</span>
                        <span>2:30 PM</span>
                        <span>3:30 PM</span>
                    </div>
                </div>

                <!-- Legend -->
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


        <!-- Message Realtor under the graph -->
        <div class="cld-box cld-message-box">
            <div class="cld-box-header">
                <span>Message Realtor</span>
                <button class="cld-send-btn">Send</button>
            </div>
            <div class="cld-box-body">
                <textarea class="cld-textarea" placeholder="type message here"></textarea>
            </div>
        </div>

    </div>
    
   <!-- RIGHT SIDE -->
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

        <!-- Header -->
        <div class="notes-header">
            <h1>Notes</h1>
            <button class="add-note-btn">+</button>
        </div>

        <!-- Sticky Notes Container -->
        <div class="sticky-notes-container"></div>

    </div>

</div>

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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const salesDisplay = document.getElementById('tpg-sales-price');
    const salesCircles = document.querySelectorAll('.sales-circle');

    // Set initial number to the last point's value
    if (salesCircles.length) {
        salesDisplay.textContent = salesCircles[salesCircles.length - 1].dataset.value;
    }

    // Update number when hovering each sales point
    salesCircles.forEach(circle => {
        circle.addEventListener('mouseenter', function () {
            salesDisplay.textContent = this.dataset.value;
        });
        circle.addEventListener('mouseleave', function () {
            // revert to last value when leaving
            salesDisplay.textContent = salesCircles[salesCircles.length - 1].dataset.value;
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rentalDisplay = document.getElementById('tpg-avg-rental-price');
    const salesDisplay  = document.getElementById('tpg-avg-sales-price');
    const propertySelect = document.getElementById('tpg-property-select');

    const rentalLine   = document.querySelector('.rental-line');
    const salesLine    = document.querySelector('.sales-line');
    const svgChart     = document.querySelector('.tpg-line-chart');

    // Store dataset for each property (example data)
    const propertyData = {
        property1: {
            rentalPoints: [ [0,210,"$2.10k"], [100,180,"$3.20k"], [200,150,"$4.80k"], [300,120,"$6.20k"], [400,80,"$7.50k"], [500,40,"$8.24k"] ],
            salesPoints:  [ [0,200,"$180k"], [100,190,"$185k"], [200,170,"$190k"], [300,140,"$200k"], [400,110,"$220k"], [500,70,"$250k"] ]
        },
        property2: {
            rentalPoints: [ [0,200,"$1.80k"], [100,170,"$2.50k"], [200,140,"$3.90k"], [300,100,"$5.00k"], [400,70,"$6.20k"], [500,50,"$7.10k"] ],
            salesPoints:  [ [0,210,"$170k"], [100,195,"$180k"], [200,180,"$190k"], [300,150,"$210k"], [400,120,"$230k"], [500,90,"$240k"] ]
        },
        property3: {
            rentalPoints: [ [0,220,"$2.50k"], [100,190,"$3.40k"], [200,170,"$4.20k"], [300,140,"$5.50k"], [400,100,"$6.80k"], [500,60,"$8.00k"] ],
            salesPoints:  [ [0,190,"$160k"], [100,185,"$170k"], [200,175,"$180k"], [300,160,"$190k"], [400,140,"$200k"], [500,120,"$210k"] ]
        }
    };

    // Utility to calculate average
    function getAverageFromPoints(points, isRental = true) {
        const nums = points.map(p => {
            let val = p[2].replace(/[$k]/gi,"");
            return parseFloat(val);
        });
        return nums.reduce((a,b)=>a+b,0) / nums.length;
    }

    // Remove old circles before adding new ones
    function clearCircles() {
        svgChart.querySelectorAll('.rental-circle, .sales-circle').forEach(el => el.remove());
    }

    // Render circles for rental & sales
    function renderCircles(points, className) {
        points.forEach(p => {
            const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            circle.setAttribute("cx", p[0]);
            circle.setAttribute("cy", p[1]);
            circle.setAttribute("r", "5");
            circle.setAttribute("class", className);
            circle.dataset.value = p[2];
            svgChart.appendChild(circle);
        });
    }

    // Update chart + summary
    function updateChart(propertyKey) {
        const data = propertyData[propertyKey];
        if (!data) return;

        // Update polyline paths
        rentalLine.setAttribute("points", data.rentalPoints.map(p => `${p[0]},${p[1]}`).join(" "));
        salesLine.setAttribute("points", data.salesPoints.map(p => `${p[0]},${p[1]}`).join(" "));

        // Clear and re-render circles
        clearCircles();
        renderCircles(data.rentalPoints, "rental-circle");
        renderCircles(data.salesPoints, "sales-circle");

        // Update averages
        rentalDisplay.textContent = `Avg Rental: $${getAverageFromPoints(data.rentalPoints).toFixed(2)}K`;
        salesDisplay.textContent  = `Avg Sales: $${getAverageFromPoints(data.salesPoints,false).toFixed(0)}K`;

        // Reattach hover for sales circles
        const salesCircles = svgChart.querySelectorAll('.sales-circle');
        salesCircles.forEach(circle => {
            circle.addEventListener('mouseenter', function () {
                salesDisplay.textContent = `Avg Sales: ${this.dataset.value}`;
            });
            circle.addEventListener('mouseleave', function () {
                salesDisplay.textContent = `Avg Sales: $${getAverageFromPoints(data.salesPoints,false).toFixed(0)}K`;
            });
        });
    }

    // Init with first property
    updateChart(propertySelect.value);

    // Listen for dropdown changes
    propertySelect.addEventListener('change', function () {
        updateChart(this.value);
    });
});
</script>

