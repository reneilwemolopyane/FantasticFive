<?php
require_once 'config.php';

// Fetch all destinations
$stmt = $connection->prepare("SELECT * FROM DESTINATION ORDER BY Country, City");
$stmt->execute();
$destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | Destinations</title>
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
        .main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 16px 32px; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .topbar h2 { font-size: 20px; font-weight: 700; color: #0f172a; }
        .topbar p  { font-size: 13px; color: #64748b; margin-top: 2px; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
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
            justify-content: center; font-weight: 700; font-size: 15px; color: #fff;
        }
        .page-body { padding: 32px; flex: 1; }

        /* DESTINATIONS GRID */
        .destinations-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .destination-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }
        .destination-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .dest-img-wrapper { position: relative; height: 200px; }
        .dest-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .dest-img-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.65) 0%, transparent 60%);
            display: flex; align-items: flex-end; padding: 16px;
        }
        .dest-img-overlay h3 { font-size: 20px; font-weight: 800; color: #fff; }
        .dest-img-overlay p  { font-size: 13px; color: rgba(255,255,255,0.85); }
        .climate-badge {
            position: absolute; top: 12px; right: 12px;
            background: rgba(0,0,0,0.5); color: #fff;
            font-size: 11px; font-weight: 600;
            padding: 4px 10px; border-radius: 20px;
        }
        .dest-body { padding: 16px; }
        .dest-description {
            font-size: 13px; color: #64748b; line-height: 1.5;
            margin-bottom: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .dest-stats {
            display: flex; gap: 12px; margin-bottom: 14px;
        }
        .dest-stat {
            background: #f8fafc; border-radius: 8px;
            padding: 8px 12px; font-size: 12px; color: #64748b;
            display: flex; align-items: center; gap: 4px;
        }
        .dest-stat strong { color: #0f172a; }
        .btn-view-packages {
            display: block; width: 100%; text-align: center;
            padding: 10px; background: #2563eb; color: #fff;
            border-radius: 8px; text-decoration: none;
            font-weight: 600; font-size: 14px; transition: background 0.2s;
        }
        .btn-view-packages:hover { background: #1d4ed8; }

        /* ATTRACTIONS + RESTAURANTS PANEL */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 200;
            align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #fff; border-radius: 16px; padding: 28px;
            width: 90%; max-width: 640px; max-height: 80vh;
            overflow-y: auto; position: relative;
        }
        .modal-close {
            position: absolute; top: 16px; right: 16px;
            background: #f1f5f9; border: none; border-radius: 50%;
            width: 32px; height: 32px; font-size: 18px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .modal h2 { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .modal .modal-sub { font-size: 13px; color: #64748b; margin-bottom: 20px; }
        .modal-section h4 {
            font-size: 14px; font-weight: 700; color: #0f172a;
            margin-bottom: 10px; padding-bottom: 6px;
            border-bottom: 2px solid #f1f5f9;
        }
        .modal-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid #f8fafc;
        }
        .modal-item:last-child { border-bottom: none; }
        .modal-item-info h5 { font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 2px; }
        .modal-item-info p  { font-size: 12px; color: #64748b; }
        .modal-item-right { font-size: 13px; font-weight: 700; color: #2563eb; white-space: nowrap; margin-left: 12px; }
        .modal-section { margin-bottom: 20px; }
        .btn-packages-modal {
            display: block; width: 100%; text-align: center;
            padding: 12px; background: #2563eb; color: #fff;
            border-radius: 8px; text-decoration: none;
            font-weight: 600; font-size: 14px; transition: background 0.2s;
            margin-top: 16px;
        }
        .btn-packages-modal:hover { background: #1d4ed8; }
        .empty-state { text-align: center; padding: 48px 20px; color: #94a3b8; }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; }
        .results-header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
        }
        .results-header h3 { font-size: 17px; font-weight: 700; color: #0f172a; }
        .results-header span { font-size: 13px; color: #64748b; }
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
        <a href="browse_destinations.php" class="nav-link active"><span class="icon">🌍</span> Destinations</a>
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

<div class="main">
    <div class="topbar">
        <div>
            <h2>🌍 Browse Destinations</h2>
            <p>Explore the world's most amazing places</p>
        </div>
        <div class="topbar-right">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" id="dest-search" placeholder="Search destinations...">
            </div>
            <div class="avatar" id="avatar-initial">?</div>
        </div>
    </div>

    <div class="page-body">

        <div class="results-header">
            <h3>All Destinations</h3>
            <span><?php echo count($destinations); ?> destinations available</span>
        </div>

        <?php if (empty($destinations)): ?>
        <div class="empty-state">
            <div class="empty-icon">🌍</div>
            <p>No destinations found.</p>
        </div>
        <?php else: ?>
        <div class="destinations-grid" id="destinations-grid">
            <?php foreach ($destinations as $dest):
                $city = $dest['City'];
                $img  = isset($dest_images[$city]) ? $dest_images[$city] : $fallback_image;

                // Fetch attraction count
                $s = $connection->prepare("SELECT COUNT(*) as cnt FROM ATTRACTION WHERE DestinationID = ?");
                $s->bind_param("i", $dest['DestinationID']);
                $s->execute();
                $att_count = $s->get_result()->fetch_assoc()['cnt'];
                $s->close();

                // Fetch restaurant count
                $s = $connection->prepare("SELECT COUNT(*) as cnt FROM RESTAURANT WHERE DestinationID = ?");
                $s->bind_param("i", $dest['DestinationID']);
                $s->execute();
                $rest_count = $s->get_result()->fetch_assoc()['cnt'];
                $s->close();

                // Fetch attractions
                $s = $connection->prepare("SELECT * FROM ATTRACTION WHERE DestinationID = ?");
                $s->bind_param("i", $dest['DestinationID']);
                $s->execute();
                $attractions = $s->get_result()->fetch_all(MYSQLI_ASSOC);
                $s->close();

                // Fetch restaurants
                $s = $connection->prepare("SELECT * FROM RESTAURANT WHERE DestinationID = ?");
                $s->bind_param("i", $dest['DestinationID']);
                $s->execute();
                $restaurants = $s->get_result()->fetch_all(MYSQLI_ASSOC);
                $s->close();

                $att_json  = htmlspecialchars(json_encode($attractions),  ENT_QUOTES);
                $rest_json = htmlspecialchars(json_encode($restaurants), ENT_QUOTES);
            ?>
            <div class="destination-card"
                 data-name="<?php echo strtolower($city . ' ' . $dest['Country']); ?>"
                 onclick="openModal(
                    '<?php echo htmlspecialchars($city, ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($dest['Country'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($dest['Climate'], ENT_QUOTES); ?>',
                    <?php echo $dest['DestinationID']; ?>,
                    '<?php echo $att_json; ?>',
                    '<?php echo $rest_json; ?>'
                 )">
                <div class="dest-img-wrapper">
                    <img src="Pictures/<?php echo $img; ?>" alt="<?php echo htmlspecialchars($city); ?>">
                    <span class="climate-badge"><?php echo htmlspecialchars($dest['Climate']); ?></span>
                    <div class="dest-img-overlay">
                        <div>
                            <h3><?php echo htmlspecialchars($city); ?></h3>
                            <p>📍 <?php echo htmlspecialchars($dest['Country']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="dest-body">
                    <?php if ($dest['Description']): ?>
                    <p class="dest-description"><?php echo htmlspecialchars($dest['Description']); ?></p>
                    <?php endif; ?>
                    <div class="dest-stats">
                        <div class="dest-stat">🎯 <strong><?php echo $att_count; ?></strong> Attractions</div>
                        <div class="dest-stat">🍽️ <strong><?php echo $rest_count; ?></strong> Restaurants</div>
                    </div>
                    <a href="browse_packages.php?destination=<?php echo urlencode($city); ?>" class="btn-view-packages">
                        View Packages →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal-overlay" onclick="closeModal(event)">
    <div class="modal">
        <button class="modal-close" onclick="closeModalBtn()">✕</button>
        <h2 id="modal-city">City</h2>
        <p class="modal-sub" id="modal-sub">Country · Climate</p>

        <div class="modal-section">
            <h4>🎯 Attractions</h4>
            <div id="modal-attractions"></div>
        </div>

        <div class="modal-section">
            <h4>🍽️ Restaurants</h4>
            <div id="modal-restaurants"></div>
        </div>

        <a href="#" id="modal-packages-btn" class="btn-packages-modal">📦 View Packages for This Destination</a>
    </div>
</div>

<script>
const user = JSON.parse(localStorage.getItem('user'));
if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}
document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();

// Search filter
document.getElementById('dest-search').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.destination-card').forEach(card => {
        card.style.display = card.dataset.name.includes(query) ? 'block' : 'none';
    });
});

// Modal
function openModal(city, country, climate, destId, attJson, restJson) {
    const attractions  = JSON.parse(attJson);
    const restaurants  = JSON.parse(restJson);

    document.getElementById('modal-city').textContent = city;
    document.getElementById('modal-sub').textContent  = country + ' · ' + climate;
    document.getElementById('modal-packages-btn').href = 'browse_packages.php?destination=' + encodeURIComponent(city);

    // Attractions
    const attDiv = document.getElementById('modal-attractions');
    if (attractions.length === 0) {
        attDiv.innerHTML = '<p style="color:#94a3b8;font-size:13px;">No attractions listed.</p>';
    } else {
        attDiv.innerHTML = attractions.map(a => `
            <div class="modal-item">
                <div class="modal-item-info">
                    <h5>${a.Name}</h5>
                    <p>${a.Category}${a.Address ? ' · ' + a.Address : ''}</p>
                </div>
                <div class="modal-item-right">
                    ${parseFloat(a.Entry_fee) > 0 ? 'R' + parseFloat(a.Entry_fee).toFixed(2) : 'Free'}
                </div>
            </div>
        `).join('');
    }

    // Restaurants
    const restDiv = document.getElementById('modal-restaurants');
    if (restaurants.length === 0) {
        restDiv.innerHTML = '<p style="color:#94a3b8;font-size:13px;">No restaurants listed.</p>';
    } else {
        restDiv.innerHTML = restaurants.map(r => `
            <div class="modal-item">
                <div class="modal-item-info">
                    <h5>${r.Name}</h5>
                    <p>${r.Cuisine_type} · ⭐ ${parseFloat(r.Rating).toFixed(1)}</p>
                </div>
                <div class="modal-item-right">~R${parseFloat(r.Avg_cost).toFixed(2)}</div>
            </div>
        `).join('');
    }

    document.getElementById('modal-overlay').classList.add('active');
}

function closeModal(e) {
    if (e.target === document.getElementById('modal-overlay')) {
        document.getElementById('modal-overlay').classList.remove('active');
    }
}
function closeModalBtn() {
    document.getElementById('modal-overlay').classList.remove('active');
}
</script>

</body>
</html>