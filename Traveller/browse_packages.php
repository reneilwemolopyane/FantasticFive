<?php
require_once 'config.php';

// ── FILTERS FROM URL ──
$filter_type        = isset($_GET['type'])        ? $_GET['type']        : '';
$filter_destination = isset($_GET['destination']) ? $_GET['destination'] : '';
$filter_sort        = isset($_GET['sort'])        ? $_GET['sort']        : 'rating';
$filter_min         = !empty($_GET['min'])         ? (int)$_GET['min']   : 0;
$filter_max         = !empty($_GET['max'])         ? (int)$_GET['max']   : 999999;

// ── BUILD QUERY ──
$where  = ["1=1"];
$params = [];
$types  = "";

if ($filter_type) {
    $where[]  = "p.Pack_type = ?";
    $params[] = $filter_type;
    $types   .= "s";
}

if ($filter_destination) {
    $where[]  = "(d.City LIKE ? OR d.Country LIKE ?)";
    $params[] = "%$filter_destination%";
    $params[] = "%$filter_destination%";
    $types   .= "ss";
}

if ($filter_min > 0 || $filter_max !== 999999) {
    $where[]  = "p.Price BETWEEN ? AND ?";
    $params[] = $filter_min;
    $params[] = $filter_max;
    $types   .= "ii";
}

$order = "p.Average_rating DESC";
if ($filter_sort === 'price_asc')  $order = "p.Price ASC";
if ($filter_sort === 'price_desc') $order = "p.Price DESC";
if ($filter_sort === 'duration')   $order = "p.Duration ASC";

$sql = "
    SELECT p.*, a.Agency_name, a.AgencyID,
           d.City, d.Country
    FROM PACKAGE p
    JOIN AGENCY a ON p.AgencyID = a.AgencyID
    JOIN PACKAGE_DESTINATION pd ON p.PackageID = pd.PackageID
    JOIN DESTINATION d ON pd.DestinationID = d.DestinationID
    WHERE " . implode(" AND ", $where) . "
    ORDER BY $order
";

$stmt = $connection->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
//testing
// echo "<pre>";
// echo "Filter: " . $filter_destination . "\n";
// echo "SQL: " . $sql . "\n";
// echo "Params: "; print_r($params);
// echo "</pre>";
//testing
$packages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Image mapping by city
$dest_images = [
    "Cape Town"   => "CPT_attraction.jpeg",
    "Paris"       => "Paris_attraction.jpeg",
    "Bali"        => "Bali_attraction.jpeg",
    "Dubai"       => "Dubai_attraction.jpeg",
    "Zanzibar"    => "Zanzibar_attraction.jpeg",
    "Tokyo"       => "Japan_package.jpeg",
    "Nairobi"     => "Safari_package.jpeg",
    "Port Louis"  => "Mauritius_package.jpeg",
];
$fallback_image = "Japan_package.jpeg";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | Browse Packages</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            display: flex;
            min-height: 100vh;
        }
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
        .sidebar-brand span { font-size: 17px; font-weight: 700; color: #ffffff; letter-spacing: 1px; }
        .sidebar-nav { flex: 1; padding: 20px 0; display: flex; flex-direction: column; gap: 4px; }
        .nav-link {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 20px; text-decoration: none;
            font-size: 14px; font-weight: 500;
            color: #94a3b8; border-left: 3px solid transparent; transition: all 0.2s;
        }
        .nav-link:hover { background: rgba(255,255,255,0.05); color: #ffffff; }
        .nav-link.active { color: #2dd4bf; border-left-color: #2dd4bf; background: rgba(45,212,191,0.08); }
        .nav-link .icon { font-size: 18px; }
        .sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,0.08); }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; font-size: 14px;
            color: #ef4444; font-weight: 500; transition: opacity 0.2s;
        }
        .sidebar-footer a:hover { opacity: 0.8; }
        .main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; }
        .topbar {
            background: #ffffff; border-bottom: 1px solid #e2e8f0;
            padding: 16px 32px; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .topbar h2 { font-size: 20px; font-weight: 700; color: #0f172a; }
        .topbar p  { font-size: 13px; color: #64748b; margin-top: 2px; }
        .avatar {
            width: 38px; height: 38px; background: #2dd4bf;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-weight: 700; font-size: 15px;
            color: #ffffff; cursor: pointer;
        }
        .page-body { padding: 32px; flex: 1; }
        .filter-bar {
            background: #ffffff; border-radius: 12px; padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 28px;
            display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;
        }
        .filter-group { display: flex; flex-direction: column; gap: 6px; flex: 1; min-width: 140px; }
        .filter-group label {
            font-size: 12px; font-weight: 600; color: #64748b;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .filter-group select,
        .filter-group input {
            padding: 9px 12px; border: 1.5px solid #e2e8f0;
            border-radius: 8px; font-size: 14px; color: #1e293b;
            font-family: inherit; outline: none; background: #f8fafc; transition: border-color 0.2s;
        }
        .filter-group select:focus,
        .filter-group input:focus {
            border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .btn-filter {
            padding: 10px 24px; background: #2563eb; color: white;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: background 0.2s; align-self: flex-end; height: 40px;
        }
        .btn-filter:hover { background: #1d4ed8; }
        .btn-reset {
            padding: 10px 16px; background: transparent; color: #64748b;
            border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 14px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            align-self: flex-end; height: 40px; text-decoration: none;
            display: flex; align-items: center;
        }
        .btn-reset:hover { border-color: #94a3b8; color: #1e293b; }
        .results-header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
        }
        .results-header h3 { font-size: 16px; font-weight: 700; color: #0f172a; }
        .results-header span { font-size: 13px; color: #64748b; }
        .packages-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
        .package-card {
            background: #ffffff; border-radius: 12px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform 0.2s, box-shadow 0.2s;
        }
        .package-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .pkg-img-wrapper { position: relative; height: 180px; }
        .pkg-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .rating-badge {
            position: absolute; top: 10px; left: 10px;
            background: #2dd4bf; color: white;
            font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        }
        .type-badge {
            position: absolute; top: 10px; right: 10px;
            background: rgba(0,0,0,0.5); color: white;
            font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px;
        }
        .card-body { padding: 16px; }
        .card-body h4 { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
        .card-destination { font-size: 13px; color: #64748b; margin-bottom: 8px; }
        .card-meta { display: flex; gap: 12px; font-size: 12px; color: #64748b; margin-bottom: 12px; }
        .price { font-size: 20px; font-weight: 700; color: #2563eb; margin-bottom: 4px; }
        .agency-name { font-size: 12px; color: #64748b; margin-bottom: 14px; }
        .card-actions { display: flex; gap: 8px; }
        .btn-details {
            flex: 1; text-align: center; padding: 9px;
            border: 1.5px solid #2dd4bf; color: #2dd4bf;
            border-radius: 6px; text-decoration: none;
            font-weight: 600; font-size: 13px; transition: all 0.2s;
        }
        .btn-details:hover { background: #2dd4bf; color: white; }
        .btn-book {
            flex: 1; text-align: center; padding: 9px;
            background: #2563eb; color: white; border-radius: 6px;
            text-decoration: none; font-weight: 600; font-size: 13px;
            transition: background 0.2s; border: none; cursor: pointer;
        }
        .btn-book:hover { background: #1d4ed8; }
        .empty-state {
            grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #94a3b8;
        }
        .empty-state .empty-icon { font-size: 52px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; margin-bottom: 16px; }
        .btn-primary {
            display: inline-block; padding: 10px 24px;
            background: #2563eb; color: white; border-radius: 8px;
            text-decoration: none; font-weight: 600; font-size: 14px; transition: background 0.2s;
        }
        .btn-primary:hover { background: #1d4ed8; }
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
        <div>
            <h2>📦 Browse Packages</h2>
            <p>Find and compare travel packages from trusted agencies</p>
        </div>
        <div class="avatar" id="avatar-initial">?</div>
    </div>

    <div class="page-body">

        <!-- FILTER BAR -->
        <form class="filter-bar" method="GET" action="browse_packages.php">
            <div class="filter-group">
                <label>Destination</label>
                <input type="text" name="destination" placeholder="City or country..."
                       value="<?php echo htmlspecialchars($filter_destination); ?>">
            </div>
            <div class="filter-group">
                <label>Package Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <?php foreach (['Adventure','Beach','Cultural','Family','Luxury','Budget'] as $t): ?>
                    <option value="<?php echo $t; ?>" <?php echo $filter_type === $t ? 'selected' : ''; ?>>
                        <?php echo $t; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Min Price (R)</label>
                <input type="number" name="min" placeholder="0" value="<?php echo $filter_min ?: ''; ?>">
            </div>
            <div class="filter-group">
                <label>Max Price (R)</label>
                <input type="number" name="max" placeholder="Any"
                       value="<?php echo $filter_max === 999999 ? '' : $filter_max; ?>">
            </div>
            <div class="filter-group">
                <label>Sort By</label>
                <select name="sort">
                    <option value="rating"     <?php echo $filter_sort === 'rating'     ? 'selected' : ''; ?>>Top Rated</option>
                    <option value="price_asc"  <?php echo $filter_sort === 'price_asc'  ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $filter_sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="duration"   <?php echo $filter_sort === 'duration'   ? 'selected' : ''; ?>>Duration</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">🔍 Search</button>
            <a href="browse_packages.php" class="btn-reset">Reset</a>
        </form>

        <!-- RESULTS HEADER -->
        <div class="results-header">
            <h3>Available Packages</h3>
            <span><?php echo count($packages); ?> package<?php echo count($packages) !== 1 ? 's' : ''; ?> found</span>
        </div>

        <!-- PACKAGES GRID -->
        <div class="packages-grid">
            <?php if (empty($packages)): ?>
            <div class="empty-state">
                <div class="empty-icon">📦</div>
                <p>No packages found matching your search.</p>
                <a href="browse_packages.php" class="btn-primary">Clear Filters</a>
            </div>
            <?php else: ?>
            <?php foreach ($packages as $pkg):
                $city    = $pkg['City'] ?? '';
                $pkg_img = isset($dest_images[$city]) ? $dest_images[$city] : $fallback_image;
            ?>
            <div class="package-card">
                <div class="pkg-img-wrapper">
                    <span class="rating-badge"><?php echo number_format($pkg['Average_rating'],1); ?> ⭐</span>
                    <span class="type-badge"><?php echo htmlspecialchars($pkg['Pack_type']); ?></span>
                    <img src="Pictures/<?php echo $pkg_img; ?>" alt="<?php echo htmlspecialchars($pkg['Title']); ?>">
                </div>
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($pkg['Title']); ?></h4>
                    <p class="card-destination">
                        📍 <?php echo htmlspecialchars(($pkg['City'] ?? '') . ', ' . ($pkg['Country'] ?? '')); ?>
                    </p>
                    <div class="card-meta">
                        <span>⏱ <?php echo $pkg['Duration']; ?> Days</span>
                    </div>
                    <p class="price">R<?php echo number_format($pkg['Price'], 2); ?></p>
                    <p class="agency-name">by <?php echo htmlspecialchars($pkg['Agency_name']); ?></p>
                    <div class="card-actions">
                        <a href="package_details.php?id=<?php echo $pkg['PackageID']; ?>" class="btn-details">View Details</a>
                        <a href="book_package.php?id=<?php echo $pkg['PackageID']; ?>" class="btn-book">Book Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</div>

<script>
const user = JSON.parse(localStorage.getItem('user'));
if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}
document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();
</script>

</body>
</html>