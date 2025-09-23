<div class="dashboard-top">

    <div class="dashboard-top-left">
      
      <div class="tpg-dashboard-container">
            <div class="tpg-tracking-section">

                <!-- Header with Dropdown -->
                <div class="tpg-tracking-header">
                    <h1 class="tpg-section-title">Tracking Property</h1>

                    <div class="tpg-tracking-summary">
                        <span class="tpg-amount" id="tpg-amount">$8.24k</span>
                        <span class="tpg-year">2025</span>
                    </div>

                    <!-- Property Dropdown -->
                    <select id="tpg-property-select">
                        <option value="property1">Property 1</option>
                        <option value="property2">Property 2</option>
                        <option value="property3">Property 3</option>
                    </select>
                </div>

                <div class="tpg-chart-container">

                    <!-- Y Axis -->
                    <div class="tpg-y-axis">
                        <span>9k</span>
                        <span>7k</span>
                        <span>5k</span>
                        <span>3k</span>
                        <span>1k</span>
                    </div>

                    <!-- Line Chart -->
                    <svg class="tpg-line-chart" viewBox="0 0 600 250" preserveAspectRatio="none">
                        <polyline id="tpg-line" points="0,210 100,180 200,150 300,120 400,80 500,40" />
                        <circle cx="0" cy="210" r="5" data-value="$2.10k"></circle>
                        <circle cx="100" cy="180" r="5" data-value="$3.20k"></circle>
                        <circle cx="200" cy="150" r="5" data-value="$4.80k"></circle>
                        <circle cx="300" cy="120" r="5" data-value="$6.20k"></circle>
                        <circle cx="400" cy="80" r="5" data-value="$7.50k"></circle>
                        <circle cx="500" cy="40" r="5" data-value="$8.24k"></circle>
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
            </div>
        </div>

        <div class="dashboard-section active-clients-section">
          <h1 class="header-title">Active Clients</h1>
          <table class="active-clients-table">
              <thead>
                  <tr>
                      <th>Client Name</th>
                      <th>Address</th>
                      <th>Closing Date</th>
                      <th>Notes</th>
                  </tr>
              </thead>
              <tbody>
                  <?php
                  $clients = ["Insurance Co", "Tech Solutions", "Green Energy", "HealthPlus", "Bright Finance", "Global Trade"];
                  $cities = ["New York", "Los Angeles", "Chicago", "Houston", "Miami", "San Francisco"];
                  $notesArr = [
                      "Follow-up required next week.",
                      "Client requested a meeting.",
                      "Pending documents.",
                      "Urgent response needed.",
                      "Initial contact completed.",
                      "Schedule demo session."
                  ];

                  for ($i = 0; $i < 6; $i++):
                      $clientName = $clients[array_rand($clients)];
                      $address = $cities[array_rand($cities)];
                      // Random closing date within next 60 days
                      $closingDate = date("d F", strtotime("+".rand(1,60)." days"));
                      $notes = $notesArr[array_rand($notesArr)];
                  ?>
                  <tr>
                      <td data-label="Client Name"><?= $clientName ?></td>
                      <td data-label="Address"><?= $address ?></td>
                      <td data-label="Closing Date"><?= $closingDate ?></td>
                      <td data-label="Notes"><?= $notes ?></td>
                  </tr>
                  <?php endfor; ?>
              </tbody>
          </table>
        </div>

        <?php include locate_template('dashboard-templates/rt/rt-leads-section.php'); ?>

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

<style>
/* Updated Tracking Property Section (Line Chart) */
.tpg-dashboard-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    margin-bottom: 20px;
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

/* X Axis */
.tpg-x-axis {
    position: absolute;
    bottom: 0;
    left: 40px;
    right: 0;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: #7f8c8d;
    font-weight: 500;
    padding-top: 5px;
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

@media screen and (max-width: 480px) {
    .tpg-dashboard-container {
        padding: 10px;
    }
}

</style>

<style>
/* General Styling */
.dashboard-section {
  padding: 20px;
  background: #fff;
  border-radius: 12px; /* Reduced from 12px */
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
  margin-bottom: 20px!important;
}

/* Add this for the calendar container */
.dashboard-top-right {
  padding: 20px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  overflow-x: auto;
  height: 100%;
}

.calendar-container {
    padding: 16px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    overflow-x: auto;
}

/* Table Styling (Desktop) */
.active-clients-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.active-clients-table th,
.active-clients-table td {
  padding: 8px;
  text-align: left;
  border-bottom: 1px solid #f5f5f5;
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
    margin-bottom: 10px;
    border-radius: 6px;
    background: #f9f9ff;
    padding: 0 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  }
  
  .active-clients-table td {
    display: flex;
    justify-content: space-between;
    padding: 6px 0; /* Reduced from 8px */
    border-bottom: 1px solid #f8f8f8; /* Lighter border color */
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
    padding: 10px; /* Reduced from 10px */
  }
  
  table {
    border-width: 0 !important;
  }
  .dashboard-top-right {
        padding: 10px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const line = document.getElementById('tpg-line');
    const circles = document.querySelectorAll('.tpg-line-chart circle');
    const amount = document.getElementById('tpg-amount');

    const data = {
        property1: { points: "0,210 100,180 200,150 300,120 400,80 500,40", values: ["$2.10k","$3.20k","$4.80k","$6.20k","$7.50k","$8.24k"], total: "$8.24k" },
        property2: { points: "0,200 100,170 200,140 300,110 400,70 500,30", values: ["$2.00k","$3.00k","$4.50k","$6.00k","$7.00k","$8.00k"], total: "$8.00k" },
        property3: { points: "0,220 100,190 200,160 300,130 400,90 500,50", values: ["$2.20k","$3.50k","$5.00k","$6.50k","$7.80k","$8.50k"], total: "$8.50k" },
    };

    function updateChart(prop) {
        line.setAttribute('points', data[prop].points);
        circles.forEach((circle, i) => {
            const coords = data[prop].points.split(" ")[i].split(",");
            circle.setAttribute('cx', coords[0]);
            circle.setAttribute('cy', coords[1]);
            circle.setAttribute('data-value', data[prop].values[i]);
        });
        amount.textContent = data[prop].total;
    }

    document.getElementById('tpg-property-select').addEventListener('change', function() {
        updateChart(this.value);
    });

    circles.forEach(point => {
        point.addEventListener('click', function() {
            alert('Value: ' + this.getAttribute('data-value'));
        });
    });
});
</script>