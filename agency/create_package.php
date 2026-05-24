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
  <title>Tripistry - Create Travel Package</title>
  <link rel="stylesheet" href="form_style.css">
  <script src="form_validation.js" defer></script>
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
        <a href="create_package.php" class="nav-item active">Create Package</a>
        <a href="manage_package.php" class="nav-item">Manage Packages</a>
        <a href="manage_booking.php" class="nav-item">Bookings</a>
        <a href="group_trips.php" class="nav-item">Group Trips</a>
        <a href="analytics.php" class="nav-item">Analytics</a>
      </div>
      <div class="nav-right">
        <span class="user-welcome">Hi, <strong><?= htmlspecialchars($agency_name) ?></strong></span>
        <a href="logout.php" class="btn-logout">Logout</a>
      </div>
    </div>
  </nav>

  <main class="main-content-wrapper">
    <div class="form-page-container">
      <div class="form-header">
        <h1>Create Travel Package</h1>
        <p class="subtitle">Broadcast a new comprehensive trip offering out to the traveler marketplace.</p>
      </div>

      <form id="packageForm" action=".../api.php" method="POST" enctype="multipart/form-data" class="package-form">
        
        <fieldset>
          <legend>Core Package Details</legend>
          <div class="form-group">
            <label for="package_name">Package Name</label>
            <input type="text" id="package_name" name="package_name" placeholder="e.g., Ultimate Luxury Cape Town Escape" required>
          </div>

          <div class="form-grid-2x">
            <div class="form-group">
              <label for="destination">Destination</label>
              <input type="text" id="destination" name="destination" placeholder="e.g., Cape Town, South Africa" required>
            </div>
            <div class="form-grid-nested">
              <div class="form-group">
                <label for="price">Price (ZAR R)</label>
                <input type="number" id="price" name="price" min="1" step="0.01" placeholder="15000.00" required>
              </div>
              <div class="form-group">
                <label for="duration">Duration (Days)</label>
                <input type="number" id="duration" name="duration" min="1" placeholder="7" required>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" placeholder="Provide a detailed itinerary overview breakdown..." required></textarea>
          </div>
        </fieldset>

        <fieldset>
          <legend>Logistics & Hospitality Requirements</legend>
          <div class="form-grid-2x">
            <div class="form-group">
              <label for="accommodation">Accommodation</label>
              <input type="text" id="accommodation" name="accommodation" placeholder="e.g., 5-Star Radisson Blu" required>
            </div>
            <div class="form-group">
              <label for="flights">Flights</label>
              <input type="text" id="flights" name="flights" placeholder="e.g., Return Economy Class Flights" required>
            </div>
          </div>

          <div class="form-grid-2x">
            <div class="form-group">
              <label for="restaurants">Restaurants</label>
              <input type="text" id="restaurants" name="restaurants" placeholder="e.g., Daily Buffet Breakfast Included" required>
            </div>
            <div class="form-group">
              <label for="transport">Transport</label>
              <input type="text" id="transport" name="transport" placeholder="e.g., Private AC Shuttle Coach" required>
            </div>
          </div>

          <div class="form-group">
            <label for="attractions">Attractions</label>
            <input type="text" id="attractions" name="attractions" placeholder="e.g., Table Mountain Cableway, Robben Island" required>
          </div>
        </fieldset>

        <fieldset>
          <legend>Visual Media Assets</legend>
          <div class="form-group">
            <label for="images">Images (Select Multiple)</label>
            <input type="file" id="images" name="images[]" multiple accept="image/*" class="file-custom-input">
            <span class="input-tip"> Tip: Hold down Ctrl or Command to pick multiple display photos.</span>
          </div>
        </fieldset>

        <div class="form-actions-wrapper">
          <button type="submit" class="btn-primary-action">Publish Travel Package</button>
        </div>
      </form>
    </div>
  </main>
</div> <?php include 'agency_footer.php'; ?>
</body>
</html>