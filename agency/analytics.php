<?php
session_start();
if (!isset($_SESSION['agency_id'])) {
    header("Location: login.php");
    exit();
}
$agency_name = $_SESSION['agency_name'] ?? 'Travel Partner';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tripistry - Analytics Engine</title>
  <link rel="stylesheet" href="analytics.css">
  <script src="analytics.js" defer></script>
</head>
<body>

  <nav class="navbar">
    <div class="nav-container">
      <a href="agency_dashboard.php" class="nav-brand">
        <img src="../frontend/Tripistry_logo.jpg" alt="Tripistry Logo" class="nav-logo">
        <span>Tripistry <small class="brand-badge">Agency</small></span>
      </a>
      <div class="nav-links">
        <a href="agency_dashboard.php" class="nav-item">Dashboard</a>
        <a href="create_package.php" class="nav-item">Create Package</a>
        <a href="manage_package.php" class="nav-item">Manage Packages</a>
        <a href="manage_booking.php" class="nav-item">Bookings</a>
        <a href="group_trips.php" class="nav-item">Group Trips</a>
        <a href="analytics.php" class="nav-item active">Analytics</a>
      </div>
      <div class="nav-right">
        <span class="user-welcome">Hi, <strong><?= htmlspecialchars($agency_name) ?></strong></span>
        <a href="logout.php" class="btn-logout">Logout</a>
      </div>
    </div>
  </nav>

  <main class="main-content-wrapper">
    <div class="section-header-flex">
      <div>
        <h1>Database Analytics Engine</h1>
        <p class="subtitle">Live database statistics driven metrics measuring performance variations.</p>
      </div>
    </div>

    <div class="analytics-grid-2x">
      <div class="chart-container-card">
        <h3>Most Booked Destinations</h3>
        <ul class="analytics-data-list">
          <li><span>Tokyo, Japan</span> <strong>45 Bookings</strong></li>
          <li><span>Cape Town, South Africa</span> <strong>38 Bookings</strong></li>
          <li><span>Port Louis, Mauritius</span> <strong>21 Bookings</strong></li>
        </ul>
      </div>

      <div class="chart-container-card">
        <h3>Booking Patterns & Performance Trends</h3>
        <p class="muted-text">Conversion efficiency status indicator tracking metrics:</p>
        <div class="trend-indicator-bar-mock">
          <span class="trend-fill" style="width: 74%;"></span>
        </div>
        <small>74% general optimization capacity matching projected target timelines.</small>
      </div>
    </div>
  </main>
</div> <?php include 'agency_footer.php'; ?>
</body>
</html>