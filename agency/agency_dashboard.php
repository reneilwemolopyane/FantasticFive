<?php include 'agency_header.php'; ?>

<link rel="stylesheet" href="agency_dashboard.css">

<div class="dashboard-container">
  <div class="dashboard-header">
    <h1>Welcome Back, <?= htmlspecialchars($agency_name) ?></h1>
    <p class="subtitle">Live database performance metrics summary overview.</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="card-icon"></div>
      <div class="card-info">
        <h3>Total Packages</h3>
        <h2 id="total-packages-count">24</h2>
      </div>
    </div>
    <div class="stat-card">
      <div class="card-icon"></div>
      <div class="card-info">
        <h3>Active Bookings</h3>
        <h2 id="active-bookings-count">18</h2>
      </div>
    </div>
    <div class="stat-card valuation">
      <div class="card-icon">🇿🇦</div>
      <div class="card-info">
        <h3>Revenue Collected</h3>
        <h2 id="revenue-sum">R248,750</h2>
      </div>
    </div>
    <div class="stat-card">
      <div class="card-icon">👥</div>
      <div class="card-info">
        <h3>Group Trips</h3>
        <h2 id="active-groups-count">3</h2>
      </div>
    </div>
  </div>

  <div class="analytics-dashboard-extension">
    <h2>Performance Matrix Analytics</h2>
    <div class="analytics-grid-2x">
      <div class="chart-container-card">
        <h3>Most Booked Destinations</h3>
        <ul class="analytics-data-list">
          <li><span>Tokyo, Japan</span> <strong>45 Bookings</strong></li>
          <li><span>Cape Town, SA</span> <strong>38 Bookings</strong></li>
          <li><span>Port Louis, Mauritius</span> <strong>21 Bookings</strong></li>
        </ul>
      </div>
      <div class="chart-container-card">
        <h3>Live Booking Patterns & Trends</h3>
        <p class="muted-text">Fulfillment target projection accuracy metrics status:</p>
        <div class="trend-indicator-bar-mock">
          <span class="trend-fill" style="width: 74%;"></span>
        </div>
        <small>74% Conversion optimization increase detected vs last quarter tracking timeline matrix.</small>
      </div>
    </div>
  </div>
</div>

</div> <?php include 'agency_footer.php'; ?>
</body>
</html>