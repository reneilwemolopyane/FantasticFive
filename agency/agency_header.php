<?php

session_start();
if (!isset($_SESSION['agency_id'])) {
    header("Location: login.php");
    exit();
}
$agency_name = $_SESSION['agency_name'] ?? 'My Agency';
?>

git clone https://github.com/reneilwemolopyane/FantasticFive.git

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tripistry Agency</title>
 <link rel="stylesheet" href="front.css" >
</head>
<body>
  <nav class="navbar">
    <a href="agency_dashboard.php" class="nav-brand">
      <img src="Tripistry_logo.jpg" alt="Logo" class="nav-logo">
      <span style="font-size:20px; font-weight:700;">Tripistry</span>
    </a>

    <div class="nav-links">
      <a href="agency_dashboard.php" class="nav-item">Dashboard</a>
      <a href="create_package.php" class="nav-item">Create Package</a>
      <a href="manage_packages.php" class="nav-item">Manage Packages</a>
      <a href="bookings.php" class="nav-item">Bookings</a>
      <a href="group_trips.php" class="nav-item">Group Trips</a>
      <a href="analytics.php" class="nav-item">Analytics</a>
    </div>

    <div class="nav-right">
      <div class="user-info">
        <span>🏢</span>
        <strong><?= htmlspecialchars($agency_name) ?></strong>
      </div>
      <a href="agency_profile.php" class="nav-item">Profile</a>
      <a href="logout.php" class="logout-btn">Logout</a>
    </div>
  </nav>

  <div style="height: 80px;"></div>