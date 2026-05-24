<?php
require_once 'config.php';

// Get package ID from URL
$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($package_id === 0) {
    header('Location: browse_packages.php');
    exit();
}

// ── FETCH PACKAGE + AGENCY ──
$stmt = $connection->prepare("
    SELECT p.*, a.Agency_name, a.Website, a.Description AS Agency_desc,
           a.Average_rating AS Agency_rating, a.AgencyID
    FROM PACKAGE p
    JOIN AGENCY a ON p.AgencyID = a.AgencyID
    WHERE p.PackageID = ?
");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$package) {
    header('Location: browse_packages.php');
    exit();
}

// ── FETCH DESTINATIONS ──
$stmt = $connection->prepare("
    SELECT d.* FROM DESTINATION d
    JOIN PACKAGE_DESTINATION pd ON d.DestinationID = pd.DestinationID
    WHERE pd.PackageID = ?
");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── FETCH FLIGHTS ──
$stmt = $connection->prepare("
    SELECT f.* FROM FLIGHT f
    JOIN PACKAGE_FLIGHT pf ON f.FlightID = pf.FlightID
    WHERE pf.PackageID = ?
");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$flights = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── FETCH ACCOMMODATION ──
$stmt = $connection->prepare("
    SELECT a.* FROM ACCOMMODATION a
    JOIN ACCOMMODATION_PACKAGE ap ON a.AccommodationID = ap.AccommodationID
    WHERE ap.PackageID = ?
");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$accommodations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── FETCH CAR ──
$stmt = $connection->prepare("SELECT * FROM CAR WHERE PackageID = ?");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── FETCH ATTRACTIONS ──
$dest_ids = array_column($destinations, 'DestinationID');
$attractions = [];
if (!empty($dest_ids)) {
    $placeholders = implode(',', array_fill(0, count($dest_ids), '?'));
    $types = str_repeat('i', count($dest_ids));
    $stmt = $connection->prepare("SELECT * FROM ATTRACTION WHERE DestinationID IN ($placeholders)");
    $stmt->bind_param($types, ...$dest_ids);
    $stmt->execute();
    $attractions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ── FETCH RESTAURANTS ──
$restaurants = [];
if (!empty($dest_ids)) {
    $placeholders = implode(',', array_fill(0, count($dest_ids), '?'));
    $types = str_repeat('i', count($dest_ids));
    $stmt = $connection->prepare("SELECT * FROM RESTAURANT WHERE DestinationID IN ($placeholders)");
    $stmt->bind_param($types, ...$dest_ids);
    $stmt->execute();
    $restaurants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ── FETCH REVIEWS ──
$stmt = $connection->prepare("
    SELECT r.*, u.Name, u.Surname
    FROM REVIEW r
    JOIN TRAVELLER t ON r.TravellerID = t.TravellerID
    JOIN USER u ON t.UserID = u.UserID
    WHERE r.PackageID = ?
    ORDER BY r.Review_date DESC
");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Image mapping
$dest_images = [
    "Cape Town"  => "CPT_attraction.jpeg",
    "Paris"      => "Paris_attraction.jpeg",
    "Bali"       => "Bali_attraction.jpeg",
    "Dubai"      => "Dubai_attraction.jpeg",
    "Zanzibar"   => "Zanzibar_attraction.jpeg",
    "Tokyo"      => "Japan_package.jpeg",
    "Nairobi"    => "Safari_package.jpeg",
    "Port Louis" => "Mauritius_package.jpeg",
];
$fallback_image = "Japan_package.jpeg";
$first_city = !empty($destinations) ? $destinations[0]['City'] : '';
$hero_img = isset($dest_images[$first_city]) ? $dest_images[$first_city] : $fallback_image;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | <?php echo htmlspecialchars($package['Title']); ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }
        /* SIDEBAR */
        .sidebar {
            width: 240px; min-height: 100vh; background: #1e293b;
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; z-index: 100;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
        }
        .sidebar-brand img { width: 36px; height: 36px; object-fit: contain; }
        .sidebar-brand span { font-size: 17px; font-weight: 700; color: #fff; letter-spacing: 1px; }
        .sidebar-nav { flex: 1; padding: 20px 0; display: flex; flex-direction: column; gap: 4px; }
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 20px; text-decoration: none;
            font-size: 14px; font-weight: 500;
            color: #94a3b8; border-left: 3px solid transparent; transition: all 0.2s;
        }
        .nav-link:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .nav-link.active { color: #2dd4bf; border-left-color: #2dd4bf; background: rgba(45,212,191,0.08); }
        .nav-link .icon { font-size: 18px; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; font-size: 14px; color: #ef4444; font-weight: 500;
        }
        .sidebar-footer a:hover { opacity: 0.8; }
        /* MAIN */
        .main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 16px 32px; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-left a {
            font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 600;
        }
        .topbar-left a:hover { text-decoration: underline; }
        .topbar-left h2 { font-size: 18px; font-weight: 700; color: #0f172a; }
        .avatar {
            width: 38px; height: 38px; background: #2dd4bf;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-weight: 700; font-size: 15px; color: #fff;
        }
        /* PAGE */
        .page-body { padding: 32px; flex: 1; }
        /* HERO */
        .hero {
            position: relative; height: 340px; border-radius: 16px;
            overflow: hidden; margin-bottom: 28px;
        }
        .hero img { width: 100%; height: 100%; object-fit: cover; }
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 60%);
            display: flex; align-items: flex-end; padding: 28px;
        }
        .hero-info h1 { font-size: 28px; font-weight: 800; color: #fff; margin-bottom: 8px; }
        .hero-badges { display: flex; gap: 10px; flex-wrap: wrap; }
        .badge {
            padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
        }
        .badge-teal  { background: #2dd4bf; color: #fff; }
        .badge-blue  { background: #2563eb; color: #fff; }
        .badge-dark  { background: rgba(0,0,0,0.5); color: #fff; }
        /* LAYOUT */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
        }
        /* CARDS */
        .info-card {
            background: #fff; border-radius: 12px; padding: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 20px;
        }
        .info-card h3 {
            font-size: 16px; font-weight: 700; color: #0f172a;
            margin-bottom: 16px; padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            display: flex; align-items: center; gap: 8px;
        }
        /* PACKAGE OVERVIEW */
        .overview-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; }
        .overview-item { background: #f8fafc; border-radius: 8px; padding: 12px 16px; }
        .overview-item p { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .overview-item h4 { font-size: 15px; font-weight: 700; color: #0f172a; }
        /* FLIGHT CARD */
        .flight-item {
            background: #f8fafc; border-radius: 8px; padding: 14px 16px;
            margin-bottom: 10px; display: flex; align-items: center; gap: 16px;
        }
        .flight-item:last-child { margin-bottom: 0; }
        .flight-icon { font-size: 24px; }
        .flight-info p { font-size: 12px; color: #64748b; margin-bottom: 2px; }
        .flight-info h4 { font-size: 14px; font-weight: 700; color: #0f172a; }
        .flight-price { margin-left: auto; font-size: 16px; font-weight: 700; color: #2563eb; }
        /* ACCOMMODATION */
        .acc-item {
            background: #f8fafc; border-radius: 8px; padding: 14px 16px;
            margin-bottom: 10px; display: flex; align-items: center; gap: 14px;
        }
        .acc-item:last-child { margin-bottom: 0; }
        .acc-icon { font-size: 24px; }
        .acc-info p { font-size: 12px; color: #64748b; margin-bottom: 2px; }
        .acc-info h4 { font-size: 14px; font-weight: 700; color: #0f172a; }
        .stars { color: #f59e0b; font-size: 13px; }
        /* ATTRACTIONS */
        .attraction-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; }
        .attraction-item { background: #f8fafc; border-radius: 8px; padding: 12px 14px; }
        .attraction-item h4 { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .attraction-item p { font-size: 12px; color: #64748b; }
        .attraction-fee { font-size: 12px; color: #2563eb; font-weight: 600; margin-top: 4px; }
        /* RESTAURANTS */
        .restaurant-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 0; border-bottom: 1px solid #f1f5f9;
        }
        .restaurant-item:last-child { border-bottom: none; }
        .restaurant-info h4 { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .restaurant-info p { font-size: 12px; color: #64748b; }
        .restaurant-cost { font-size: 14px; font-weight: 700; color: #2563eb; }
        /* CAR */
        .car-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
        .car-item { background: #f8fafc; border-radius: 8px; padding: 12px 14px; text-align: center; }
        .car-item p { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
        .car-item h4 { font-size: 14px; font-weight: 700; color: #0f172a; }
        /* REVIEWS */
        .review-item { padding: 16px 0; border-bottom: 1px solid #f1f5f9; }
        .review-item:last-child { border-bottom: none; }
        .review-header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .review-avatar {
            width: 34px; height: 34px; background: #2dd4bf;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-weight: 700; font-size: 13px; color: #fff;
        }
        .review-name { font-size: 14px; font-weight: 700; color: #0f172a; }
        .review-date { font-size: 12px; color: #94a3b8; }
        .review-stars { color: #f59e0b; font-size: 14px; margin-bottom: 6px; }
        .review-comment { font-size: 14px; color: #475569; line-height: 1.5; }
        .empty-section { text-align: center; padding: 24px; color: #94a3b8; font-size: 14px; }
        /* STICKY BOOKING CARD */
        .booking-card {
            background: #fff; border-radius: 12px; padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            position: sticky; top: 80px;
        }
        .booking-card h3 { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .booking-price { font-size: 32px; font-weight: 800; color: #2563eb; margin-bottom: 4px; }
        .booking-price span { font-size: 14px; font-weight: 500; color: #64748b; }
        .booking-rating { font-size: 13px; color: #64748b; margin-bottom: 20px; }
        .booking-divider { border: none; border-top: 1px solid #e2e8f0; margin: 16px 0; }
        .booking-detail { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 10px; }
        .booking-detail span:first-child { color: #64748b; }
        .booking-detail span:last-child { font-weight: 600; color: #0f172a; }
        .btn-book-now {
            width: 100%; padding: 14px; background: #2563eb; color: #fff;
            border: none; border-radius: 10px; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: background 0.2s; margin-top: 16px;
        }
        .btn-book-now:hover { background: #1d4ed8; }
        .btn-review {
            width: 100%; padding: 11px; background: transparent;
            border: 1.5px solid #2dd4bf; color: #2dd4bf;
            border-radius: 10px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; margin-top: 10px;
        }
        .btn-review:hover { background: #2dd4bf; color: #fff; }
        .agency-box {
            background: #f8fafc; border-radius: 10px; padding: 14px; margin-top: 16px;
        }
        .agency-box p { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
        .agency-box h4 { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .agency-box a { font-size: 13px; color: #2563eb; text-decoration: none; }
        .agency-box a:hover { text-decoration: underline; }
        .agency-rating { font-size: 13px; color: #64748b; margin-top: 4px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <a href="traveller_dashboard.php" class="sidebar-brand">
        <img src="Pictures/Tripistry_logo.jpg" alt="Tripistry Logo">
        <span>Tripistry</span>
    </a>
    <nav class="sidebar-nav">
        <a href="traveller_dashboard.php" class="nav-link"><span class="icon">🏠</span> Dashboard</a>
        <a href="browse_destinations.php" class="nav-link"><span class="icon">🌍</span> Destinations</a>
        <a href="browse_packages.php" class="nav-link active"><span class="icon">📦</span> Browse Packages</a>
        <a href="group_trips.php" class="nav-link"><span class="icon">👥</span> Group Trips</a>
        <a href="my_bookings.php" class="nav-link"><span class="icon">🧳</span> My Bookings</a>
        <a href="my_reviews.php" class="nav-link"><span class="icon">⭐</span> My Reviews</a>
        <a href="traveller_profile.php" class="nav-link"><span class="icon">👤</span> Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php"><span>🚪</span> Logout</a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <a href="browse_packages.php">← Back to Packages</a>
            <h2><?php echo htmlspecialchars($package['Title']); ?></h2>
        </div>
        <div class="avatar" id="avatar-initial">?</div>
    </div>

    <div class="page-body">

        <!-- HERO -->
        <div class="hero">
            <img src="Pictures/<?php echo $hero_img; ?>" alt="<?php echo htmlspecialchars($package['Title']); ?>">
            <div class="hero-overlay">
                <div class="hero-info">
                    <h1><?php echo htmlspecialchars($package['Title']); ?></h1>
                    <div class="hero-badges">
                        <span class="badge badge-teal"><?php echo htmlspecialchars($package['Pack_type']); ?></span>
                        <span class="badge badge-blue">⏱ <?php echo $package['Duration']; ?> Days</span>
                        <span class="badge badge-dark">⭐ <?php echo number_format($package['Average_rating'],1); ?></span>
                        <?php foreach ($destinations as $d): ?>
                        <span class="badge badge-dark">📍 <?php echo htmlspecialchars($d['City'] . ', ' . $d['Country']); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENT GRID -->
        <div class="content-grid">

            <!-- LEFT COLUMN -->
            <div class="left-col">

                <!-- OVERVIEW -->
                <div class="info-card">
                    <h3>📋 Package Overview</h3>
                    <div class="overview-grid">
                        <div class="overview-item">
                            <p>Start Date</p>
                            <h4><?php echo $package['Start_date']; ?></h4>
                        </div>
                        <div class="overview-item">
                            <p>End Date</p>
                            <h4><?php echo $package['End_date']; ?></h4>
                        </div>
                        <div class="overview-item">
                            <p>Duration</p>
                            <h4><?php echo $package['Duration']; ?> Days</h4>
                        </div>
                        <div class="overview-item">
                            <p>Max People</p>
                            <h4><?php echo $package['Max_people']; ?> Travellers</h4>
                        </div>
                    </div>
                    <?php if ($package['Description']): ?>
                    <p style="margin-top:16px; font-size:14px; color:#475569; line-height:1.6;">
                        <?php echo htmlspecialchars($package['Description']); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- FLIGHTS -->
                <div class="info-card">
                    <h3>✈️ Flights Included</h3>
                    <?php if (empty($flights)): ?>
                    <p class="empty-section">No flights listed for this package.</p>
                    <?php else: ?>
                    <?php foreach ($flights as $f): ?>
                    <div class="flight-item">
                        <div class="flight-icon">✈️</div>
                        <div class="flight-info">
                            <p><?php echo htmlspecialchars($f['Airline']); ?></p>
                            <h4><?php echo htmlspecialchars($f['Departure_airport']); ?> → <?php echo htmlspecialchars($f['Arrival_airport']); ?></h4>
                        </div>
                        <div class="flight-price">R<?php echo number_format($f['Price'],2); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- ACCOMMODATION -->
                <div class="info-card">
                    <h3>🏨 Accommodation</h3>
                    <?php if (empty($accommodations)): ?>
                    <p class="empty-section">No accommodation listed for this package.</p>
                    <?php else: ?>
                    <?php foreach ($accommodations as $a): ?>
                    <div class="acc-item">
                        <div class="acc-icon">🏨</div>
                        <div class="acc-info">
                            <h4><?php echo htmlspecialchars($a['Name']); ?></h4>
                            <p><?php echo htmlspecialchars($a['Acc_type']); ?> · <?php echo htmlspecialchars($a['Street_name']); ?></p>
                            <div class="stars"><?php echo str_repeat('★', $a['Star_rating']) . str_repeat('☆', 5 - $a['Star_rating']); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- CAR RENTAL -->
                <?php if ($car): ?>
                <div class="info-card">
                    <h3>🚗 Car Rental</h3>
                    <div class="car-grid">
                        <div class="car-item">
                            <p>Vehicle</p>
                            <h4><?php echo htmlspecialchars($car['Manufacturer'] . ' ' . $car['Model']); ?></h4>
                        </div>
                        <div class="car-item">
                            <p>Category</p>
                            <h4><?php echo htmlspecialchars($car['Category']); ?></h4>
                        </div>
                        <div class="car-item">
                            <p>Seats</p>
                            <h4><?php echo $car['Seats']; ?></h4>
                        </div>
                        <div class="car-item">
                            <p>Transmission</p>
                            <h4><?php echo htmlspecialchars($car['Transmission']); ?></h4>
                        </div>
                        <div class="car-item">
                            <p>Per Day</p>
                            <h4>R<?php echo number_format($car['Price_per_day'],2); ?></h4>
                        </div>
                        <div class="car-item">
                            <p>Total Cost</p>
                            <h4>R<?php echo number_format($car['Total_rental_cost'],2); ?></h4>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ATTRACTIONS -->
                <div class="info-card">
                    <h3>🎯 Attractions</h3>
                    <?php if (empty($attractions)): ?>
                    <p class="empty-section">No attractions listed for this destination.</p>
                    <?php else: ?>
                    <div class="attraction-grid">
                        <?php foreach ($attractions as $att): ?>
                        <div class="attraction-item">
                            <h4><?php echo htmlspecialchars($att['Name']); ?></h4>
                            <p><?php echo htmlspecialchars($att['Category']); ?></p>
                            <p class="attraction-fee">
                                <?php echo $att['Entry_fee'] > 0 ? 'R' . number_format($att['Entry_fee'],2) : 'Free Entry'; ?>
                            </p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- RESTAURANTS -->
                <div class="info-card">
                    <h3>🍽️ Restaurants</h3>
                    <?php if (empty($restaurants)): ?>
                    <p class="empty-section">No restaurants listed for this destination.</p>
                    <?php else: ?>
                    <?php foreach ($restaurants as $r): ?>
                    <div class="restaurant-item">
                        <div class="restaurant-info">
                            <h4><?php echo htmlspecialchars($r['Name']); ?></h4>
                            <p><?php echo htmlspecialchars($r['Cuisine_type']); ?> · ⭐ <?php echo number_format($r['Rating'],1); ?></p>
                        </div>
                        <div class="restaurant-cost">~R<?php echo number_format($r['Avg_cost'],2); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- REVIEWS -->
                <div class="info-card">
                    <h3>⭐ Reviews (<?php echo count($reviews); ?>)</h3>
                    <?php if (empty($reviews)): ?>
                    <p class="empty-section">No reviews yet. Be the first to review!</p>
                    <?php else: ?>
                    <?php foreach ($reviews as $rev): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div class="review-avatar"><?php echo strtoupper(substr($rev['Name'],0,1)); ?></div>
                            <div>
                                <div class="review-name"><?php echo htmlspecialchars($rev['Name'] . ' ' . $rev['Surname']); ?></div>
                                <div class="review-date"><?php echo $rev['Review_date']; ?></div>
                            </div>
                        </div>
                        <div class="review-stars">
                            <?php echo str_repeat('★', $rev['Rating']) . str_repeat('☆', 5 - $rev['Rating']); ?>
                        </div>
                        <p class="review-comment"><?php echo htmlspecialchars($rev['Comment']); ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div><!-- end left col -->

            <!-- RIGHT COLUMN - STICKY BOOKING CARD -->
            <div class="right-col">
                <div class="booking-card">
                    <h3>Book This Package</h3>
                    <div class="booking-price">
                        R<?php echo number_format($package['Price'],2); ?>
                        <span>per person</span>
                    </div>
                    <div class="booking-rating">
                        ⭐ <?php echo number_format($package['Average_rating'],1); ?> · <?php echo count($reviews); ?> reviews
                    </div>
                    <hr class="booking-divider">
                    <div class="booking-detail">
                        <span>Duration</span>
                        <span><?php echo $package['Duration']; ?> Days</span>
                    </div>
                    <div class="booking-detail">
                        <span>Start Date</span>
                        <span><?php echo $package['Start_date']; ?></span>
                    </div>
                    <div class="booking-detail">
                        <span>End Date</span>
                        <span><?php echo $package['End_date']; ?></span>
                    </div>
                    <div class="booking-detail">
                        <span>Max People</span>
                        <span><?php echo $package['Max_people']; ?></span>
                    </div>
                    <div class="booking-detail">
                        <span>Type</span>
                        <span><?php echo htmlspecialchars($package['Pack_type']); ?></span>
                    </div>

                    <button class="btn-book-now" onclick="window.location='book_package.php?id=<?php echo $package['PackageID']; ?>'">
                        🧳 Book Now
                    </button>
                    <button class="btn-review" onclick="window.location='my_reviews.php?package_id=<?php echo $package['PackageID']; ?>'">
                        ⭐ Leave a Review
                    </button>

                    <!-- AGENCY INFO -->
                    <div class="agency-box">
                        <p>Travel Agency</p>
                        <h4><?php echo htmlspecialchars($package['Agency_name']); ?></h4>
                        <?php if ($package['Website']): ?>
                        <a href="<?php echo htmlspecialchars($package['Website']); ?>" target="_blank">
                            🌐 Visit Website
                        </a>
                        <?php endif; ?>
                        <div class="agency-rating">
                            ⭐ <?php echo number_format($package['Agency_rating'],1); ?> Agency Rating
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end content grid -->
    </div><!-- end page body -->
</div><!-- end main -->

<script>
const user = JSON.parse(localStorage.getItem('user'));
if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}
document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();
</script>

</body>
</html>