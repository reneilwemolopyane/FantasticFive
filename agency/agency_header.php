<?php
session_start();
/*if (!isset($_SESSION['agency_id'])) {
    header("Location: login.php");
    exit();
}*/
$agency_name = $_SESSION['agency_name'] ?? 'Travel Partner';


$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tripistry Agency Portal</title>
 
</head>
<body>

  <nav class="navbar">
    <div class="nav-container">
      <a href="agency_dashboard.php" class="nav-brand">
        <img src="../frontend/Tripistry_logo.jpg" alt="Tripistry Logo" class="nav-logo">
        <span>Tripistry <small class="brand-badge">Agency</small></span>
      </a>

      <div class="nav-links">
        <a href="agency_dashboard.php" class="nav-item <?= $current_page == 'agency_dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="create_package.php" class="nav-item <?= $current_page == 'create_package.php' ? 'active' : '' ?>">Create Package</a>
        <a href="manage_package.php" class="nav-item <?= $current_page == 'manage_package.php' ? 'active' : '' ?>">Manage Packages</a>
        <a href="manage_booking.php" class="nav-item <?= $current_page == 'manage_booking.php' ? 'active' : '' ?>">Bookings</a>
        <a href="group_trips.php" class="nav-item <?= $current_page == 'group_trips.php' ? 'active' : '' ?>">Group Trips</a>
        <a href="analytics.php" class="nav-item <?= $current_page == 'analytics.php' ? 'active' : '' ?>">Analytics</a>
      </div>

      <div class="nav-right">
        <span class="user-welcome">Hi, <strong><?= htmlspecialchars($agency_name) ?></strong></span>
        <a href="logout.php" class="btn-logout">Logout</a>
      </div>
    </div>
  </nav>

  <main class="main-content-wrapper">