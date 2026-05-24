<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | Dashboard</title>
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
            width: 240px;
            min-height: 100vh;
            background: #1e293b;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
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
            color: #94a3b8; border-left: 3px solid transparent;
            transition: all 0.2s;
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
        .main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar {
            background: #ffffff; border-bottom: 1px solid #e2e8f0;
            padding: 16px 32px; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .topbar-left h2 { font-size: 20px; font-weight: 700; color: #0f172a; }
        .topbar-left p  { font-size: 13px; color: #64748b; margin-top: 2px; }
        .topbar-right   { display: flex; align-items: center; gap: 16px; }
        .search-bar {
            display: flex; align-items: center;
            background: #f1f5f9; border: 1.5px solid #e2e8f0;
            border-radius: 8px; padding: 8px 14px; gap: 8px; width: 260px;
        }
        .search-bar input {
            border: none; background: transparent; outline: none;
            font-size: 14px; color: #1e293b; width: 100%; font-family: inherit;
        }
        .search-bar input::placeholder { color: #94a3b8; }
        .avatar {
            width: 38px; height: 38px; background: #2dd4bf;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-weight: 700; font-size: 15px;
            color: #ffffff; cursor: pointer;
        }
        .page-body { padding: 32px; flex: 1; }
        .stats-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card {
            background: #ffffff; border-radius: 12px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            display: flex; align-items: center; gap: 16px;
        }
        .stat-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .stat-icon.blue  { background: #eff6ff; }
        .stat-icon.teal  { background: #f0fdfa; }
        .stat-icon.amber { background: #fffbeb; }
        .stat-icon.rose  { background: #fff1f2; }
        .stat-info p  { font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 4px; }
        .stat-info h3 { font-size: 22px; font-weight: 700; color: #0f172a; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .section-header h3 { font-size: 17px; font-weight: 700; color: #0f172a; }
        .section-header a { font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 600; }
        .section-header a:hover { text-decoration: underline; }
        .destinations-grid { display: grid; grid-template-columns: repeat(5,1fr); gap: 16px; margin-bottom: 36px; }
        .destination-card {
            position: relative; height: 180px; border-radius: 12px;
            overflow: hidden; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .destination-card img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease; }
        .destination-card:hover img { transform: scale(1.07); }
        .dest-label {
            position: absolute; bottom: 0; left: 0; right: 0;
            padding: 10px 12px;
            background: linear-gradient(transparent, rgba(0,0,0,0.72));
            color: white;
        }
        .dest-label h4 { font-size: 14px; font-weight: 700; margin-bottom: 2px; }
        .dest-label p  { font-size: 11px; opacity: 0.85; }
        .packages-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; margin-bottom: 36px; }
        .package-card {
            background: #ffffff; border-radius: 12px; overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: transform 0.2s, box-shadow 0.2s;
        }
        .package-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .pkg-img-wrapper { position: relative; height: 170px; }
        .pkg-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .rating-badge {
            position: absolute; top: 10px; left: 10px;
            background: #2dd4bf; color: white;
            font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
        }
        .pkg-type-badge {
            position: absolute; top: 10px; right: 10px;
            background: rgba(0,0,0,0.5); color: white;
            font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px;
        }
        .card-body { padding: 14px; }
        .card-body h4 { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
        .card-meta { display: flex; gap: 12px; font-size: 12px; color: #64748b; margin-bottom: 10px; }
        .price { font-size: 18px; font-weight: 700; color: #2563eb; margin-bottom: 4px; }
        .agency-name { font-size: 12px; color: #64748b; margin-bottom: 12px; }
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
        .bookings-table-wrapper {
            background: #ffffff; border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            overflow: hidden; margin-bottom: 36px;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8fafc; }
        th {
            padding: 12px 16px; font-size: 12px; font-weight: 700;
            color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;
            text-align: left; border-bottom: 1px solid #e2e8f0;
        }
        td { padding: 14px 16px; font-size: 14px; color: #1e293b; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-confirmed { background: #dcfce7; color: #16a34a; }
        .status-pending   { background: #fef9c3; color: #ca8a04; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .empty-state { text-align: center; padding: 48px 20px; color: #94a3b8; }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; }
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

<!-- SIDEBAR -->
<aside class="sidebar">
    <a href="traveller_dashboard.php" class="sidebar-brand">
        <img src="Pictures/Tripistry_logo.jpg" alt="Tripistry Logo">
        <span>Tripistry</span>
    </a>
    <nav class="sidebar-nav">
        <a href="traveller_dashboard.php" class="nav-link active"><span class="icon">🏠</span> Dashboard</a>
        <a href="browse_destinations.php" class="nav-link"><span class="icon">🌍</span> Destinations</a>
        <a href="browse_packages.php" class="nav-link"><span class="icon">📦</span> Browse Packages</a>
        <a href="group_trips.php" class="nav-link"><span class="icon">👥</span> Group Trips</a>
        <a href="my_bookings.php" class="nav-link"><span class="icon">🧳</span> My Bookings</a>
        <a href="my_reviews.php" class="nav-link"><span class="icon">⭐</span> My Reviews</a>
        <a href="traveller_profile.php" class="nav-link"><span class="icon">👤</span> Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php"><span>🚪</span> Logout</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="topbar-left">
            <h2 id="welcome-msg">Welcome back! ✈️</h2>
            <p>Ready to plan your next adventure?</p>
        </div>
        <div class="topbar-right">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" placeholder="Search destinations, packages...">
            </div>
            <div class="avatar" id="avatar-initial">?</div>
        </div>
    </div>

    <div class="page-body">

        <!-- STATS -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon blue">🧳</div>
                <div class="stat-info">
                    <p>Total Bookings</p>
                    <h3 id="stat-bookings">...</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon teal">🌍</div>
                <div class="stat-info">
                    <p>Destinations Visited</p>
                    <h3>0</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">⭐</div>
                <div class="stat-info">
                    <p>Reviews Left</p>
                    <h3 id="stat-reviews">...</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon rose">👥</div>
                <div class="stat-info">
                    <p>Group Trips Joined</p>
                    <h3 id="stat-groups">...</h3>
                </div>
            </div>
        </div>

        <!-- DESTINATIONS -->
        <div class="section-header">
            <h3>🌍 Popular Destinations</h3>
            <a href="browse_destinations.php">View All →</a>
        </div>
        <div class="destinations-grid">
            <?php
            $stmt = $connection->prepare("SELECT * FROM DESTINATION LIMIT 5");
            $stmt->execute();
            $result = $stmt->get_result();
            $destinations = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $dest_images = [
                "Cape Town"  => "CPT_attraction.jpeg",
                "Paris"      => "Paris_attraction.jpeg",
                "Bali"       => "Bali_attraction.jpeg",
                "Dubai"      => "Dubai_attraction.jpeg",
                "Zanzibar"   => "Zanzibar_attraction.jpeg",
            ];
            $fallback_image = "Japan_package.jpeg";

            foreach ($destinations as $dest):
                $city = $dest['City'];
                $img = isset($dest_images[$city]) ? $dest_images[$city] : $fallback_image;
            ?>
            <div class="destination-card" onclick="window.location='browse_packages.php?destination=<?php echo urlencode($city); ?>'">
                <img src="Pictures/<?php echo $img; ?>" alt="<?php echo htmlspecialchars($city); ?>">
                <div class="dest-label">
                    <h4><?php echo htmlspecialchars($city); ?></h4>
                    <p>📍 <?php echo htmlspecialchars($dest['Country']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- FEATURED PACKAGES -->
        <div class="section-header">
            <h3>📦 Featured Packages</h3>
            <a href="browse_packages.php">View All →</a>
        </div>
        <div class="packages-grid">
            <?php
            $stmt = $connection->prepare("
                SELECT p.*, a.Agency_name,
                       d.City, d.Country
                FROM PACKAGE p
                JOIN AGENCY a ON p.AgencyID = a.AgencyID
                LEFT JOIN PACKAGE_DESTINATION pd ON p.PackageID = pd.PackageID
                LEFT JOIN DESTINATION d ON pd.DestinationID = d.DestinationID
                ORDER BY p.Average_rating DESC
                LIMIT 3
            ");
            $stmt->execute();
            $packages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            foreach ($packages as $pkg):
                $city = $pkg['City'] ?? '';
                $pkg_img = isset($dest_images[$city]) ? $dest_images[$city] : $fallback_image;
            ?>
            <div class="package-card">
                <div class="pkg-img-wrapper">
                    <span class="rating-badge"><?php echo number_format($pkg['Average_rating'],1); ?> ⭐</span>
                    <span class="pkg-type-badge"><?php echo htmlspecialchars($pkg['Pack_type']); ?></span>
                    <img src="Pictures/<?php echo $pkg_img; ?>" alt="<?php echo htmlspecialchars($pkg['Title']); ?>">
                </div>
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($pkg['Title']); ?></h4>
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
        </div>

        <!-- RECENT BOOKINGS -->
        <div class="section-header">
            <h3>🧳 Recent Bookings</h3>
            <a href="my_bookings.php">View All →</a>
        </div>
        <div class="bookings-table-wrapper" id="bookings-wrapper">
            <div class="empty-state">
                <div class="empty-icon">🧳</div>
                <p>Loading your bookings...</p>
            </div>
        </div>

    </div>
</div>

<script>
// Read user from localStorage
const user = JSON.parse(localStorage.getItem('user'));

if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}

// Set welcome message and avatar
document.getElementById('welcome-msg').textContent = 'Welcome back, ' + user.name + '! ✈️';
document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();

const travellerID = user.traveller_id;

// Fetch stats and bookings via API
async function loadStats() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'GetTravellerStats',
            apikey: user.apikey,
            traveller_id: travellerID
        })
    });
    const result = await res.json();
    if (result.status === 'success') {
        document.getElementById('stat-bookings').textContent = result.data.bookings ?? 0;
        document.getElementById('stat-reviews').textContent  = result.data.reviews  ?? 0;
        document.getElementById('stat-groups').textContent   = result.data.groups   ?? 0;
    }
}

async function loadBookings() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'GetRecentBookings',
            apikey: user.apikey,
            traveller_id: travellerID
        })
    });
    const result = await res.json();
    const wrapper = document.getElementById('bookings-wrapper');

    if (result.status === 'success' && result.data.length > 0) {
        let rows = result.data.map(b => `
            <tr>
                <td>${b.Title}</td>
                <td>${b.Agency_name}</td>
                <td>${b.Start_date}</td>
                <td>${b.End_date}</td>
                <td><span class="status-badge status-confirmed">Confirmed</span></td>
            </tr>
        `).join('');
        wrapper.innerHTML = `
            <table>
                <thead>
                    <tr>
                        <th>Package</th><th>Agency</th>
                        <th>Start Date</th><th>End Date</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>`;
    } else {
        wrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🧳</div>
                <p>You haven't booked any packages yet.</p>
                <a href="browse_packages.php" class="btn-primary">Browse Packages</a>
            </div>`;
    }
}

loadStats();
loadBookings();
</script>
</body>
</html>