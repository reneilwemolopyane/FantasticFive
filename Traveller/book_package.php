<?php
require_once 'config.php';

$package_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($package_id === 0) {
    header('Location: browse_packages.php');
    exit();
}

// Fetch package + agency
$stmt = $connection->prepare("
    SELECT p.*, a.Agency_name, a.Average_rating
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

// Fetch destination
$stmt = $connection->prepare("
    SELECT d.City, d.Country FROM DESTINATION d
    JOIN PACKAGE_DESTINATION pd ON d.DestinationID = pd.DestinationID
    WHERE pd.PackageID = ?
    LIMIT 1
");
$stmt->bind_param("i", $package_id);
$stmt->execute();
$destination = $stmt->get_result()->fetch_assoc();
$stmt->close();

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
$city     = $destination['City'] ?? '';
$hero_img = isset($dest_images[$city]) ? $dest_images[$city] : "Japan_package.jpeg";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | Book Package</title>
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
        .main { margin-left: 240px; flex: 1; display: flex; flex-direction: column; }
        .topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 16px 32px; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 50;
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-left a { font-size: 13px; color: #2563eb; text-decoration: none; font-weight: 600; }
        .topbar-left a:hover { text-decoration: underline; }
        .topbar-left h2 { font-size: 18px; font-weight: 700; color: #0f172a; }
        .avatar {
            width: 38px; height: 38px; background: #2dd4bf;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-weight: 700; font-size: 15px; color: #fff;
        }
        .page-body { padding: 32px; flex: 1; }

        /* LAYOUT */
        .booking-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 28px;
            max-width: 1100px;
        }

        /* PACKAGE SUMMARY CARD */
        .summary-card {
            background: #fff; border-radius: 14px;
            overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-bottom: 20px;
        }
        .summary-img { width: 100%; height: 200px; object-fit: cover; }
        .summary-body { padding: 20px; }
        .summary-body h3 { font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
        .summary-agency { font-size: 13px; color: #64748b; margin-bottom: 14px; }
        .summary-grid { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; }
        .summary-item { background: #f8fafc; border-radius: 8px; padding: 10px 14px; }
        .summary-item p { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; margin-bottom: 3px; }
        .summary-item h4 { font-size: 14px; font-weight: 700; color: #0f172a; }

        /* BOOKING FORM CARD */
        .form-card {
            background: #fff; border-radius: 14px; padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
        }
        .form-card h3 {
            font-size: 16px; font-weight: 700; color: #0f172a;
            margin-bottom: 20px; padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
        }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 13px; font-weight: 600;
            color: #344054; margin-bottom: 6px;
        }
        .form-group input,
        .form-group select {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; color: #1e293b; font-family: inherit;
            outline: none; background: #f8fafc; transition: border-color 0.2s;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
            background: #fff;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .error-msg { color: #ef4444; font-size: 12px; margin-top: 4px; display: none; }

        /* PRICE BREAKDOWN */
        .price-card {
            background: #fff; border-radius: 14px; padding: 24px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            position: sticky; top: 80px;
        }
        .price-card h3 { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 16px; }
        .price-line {
            display: flex; justify-content: space-between;
            font-size: 14px; padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .price-line:last-of-type { border-bottom: none; }
        .price-line span:first-child { color: #64748b; }
        .price-line span:last-child  { font-weight: 600; color: #0f172a; }
        .price-total {
            display: flex; justify-content: space-between;
            font-size: 18px; font-weight: 800;
            padding-top: 14px; margin-top: 8px;
            border-top: 2px solid #e2e8f0;
            color: #0f172a;
        }
        .price-total span:last-child { color: #2563eb; }
        .btn-confirm {
            width: 100%; padding: 14px; background: #2563eb; color: #fff;
            border: none; border-radius: 10px; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: background 0.2s; margin-top: 20px;
        }
        .btn-confirm:hover { background: #1d4ed8; }
        .btn-confirm:disabled { background: #94a3b8; cursor: not-allowed; }
        .secure-note {
            text-align: center; font-size: 12px; color: #94a3b8; margin-top: 12px;
        }

        /* SUCCESS MODAL */
        .modal-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 200;
            align-items: center; justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: #fff; border-radius: 20px; padding: 40px;
            text-align: center; max-width: 420px; width: 90%;
        }
        .modal .success-icon { font-size: 56px; margin-bottom: 16px; }
        .modal h2 { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .modal p  { font-size: 14px; color: #64748b; margin-bottom: 24px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 10px; justify-content: center; }
        .btn-modal-primary {
            padding: 11px 24px; background: #2563eb; color: #fff;
            border-radius: 8px; text-decoration: none;
            font-weight: 600; font-size: 14px;
        }
        .btn-modal-secondary {
            padding: 11px 24px; border: 1.5px solid #e2e8f0; color: #64748b;
            border-radius: 8px; text-decoration: none;
            font-weight: 600; font-size: 14px;
        }
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
            <a href="package_details.php?id=<?php echo $package_id; ?>">← Back to Package</a>
            <h2>Book Package</h2>
        </div>
        <div class="avatar" id="avatar-initial">?</div>
    </div>

    <div class="page-body">
        <div class="booking-layout">

            <!-- LEFT COLUMN -->
            <div>
                <!-- PACKAGE SUMMARY -->
                <div class="summary-card">
                    <img class="summary-img" src="Pictures/<?php echo $hero_img; ?>" alt="<?php echo htmlspecialchars($package['Title']); ?>">
                    <div class="summary-body">
                        <h3><?php echo htmlspecialchars($package['Title']); ?></h3>
                        <p class="summary-agency">by <?php echo htmlspecialchars($package['Agency_name']); ?> · ⭐ <?php echo number_format($package['Average_rating'],1); ?></p>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <p>Destination</p>
                                <h4><?php echo htmlspecialchars(($destination['City'] ?? '') . ', ' . ($destination['Country'] ?? '')); ?></h4>
                            </div>
                            <div class="summary-item">
                                <p>Duration</p>
                                <h4><?php echo $package['Duration']; ?> Days</h4>
                            </div>
                            <div class="summary-item">
                                <p>Start Date</p>
                                <h4><?php echo $package['Start_date']; ?></h4>
                            </div>
                            <div class="summary-item">
                                <p>End Date</p>
                                <h4><?php echo $package['End_date']; ?></h4>
                            </div>
                            <div class="summary-item">
                                <p>Package Type</p>
                                <h4><?php echo htmlspecialchars($package['Pack_type']); ?></h4>
                            </div>
                            <div class="summary-item">
                                <p>Max People</p>
                                <h4><?php echo $package['Max_people']; ?></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BOOKING FORM -->
                <div class="form-card">
                    <h3>📋 Your Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="fname" placeholder="John" readonly>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="lname" placeholder="Doe" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="email" placeholder="john@example.com" readonly>
                    </div>
                    <div class="form-group">
                        <label>Number of Travellers</label>
                        <select id="num-travellers" onchange="updateTotal()">
                            <?php for ($i = 1; $i <= $package['Max_people']; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?> Traveller<?php echo $i > 1 ? 's' : ''; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Special Requests (optional)</label>
                        <input type="text" id="special-requests" placeholder="Any special requirements...">
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN - PRICE BREAKDOWN -->
            <div>
                <div class="price-card">
                    <h3>💰 Price Breakdown</h3>
                    <div class="price-line">
                        <span>Price per person</span>
                        <span>R<?php echo number_format($package['Price'], 2); ?></span>
                    </div>
                    <div class="price-line">
                        <span>Number of travellers</span>
                        <span id="num-display">1</span>
                    </div>
                    <div class="price-line">
                        <span>Duration</span>
                        <span><?php echo $package['Duration']; ?> Days</span>
                    </div>
                    <div class="price-total">
                        <span>Total</span>
                        <span id="total-price">R<?php echo number_format($package['Price'], 2); ?></span>
                    </div>

                    <button class="btn-confirm" id="btn-confirm" onclick="confirmBooking()">
                        🧳 Confirm Booking
                    </button>
                    <p class="secure-note">🔒 Your booking is secure and confirmed instantly</p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- SUCCESS MODAL -->
<div class="modal-overlay" id="success-modal">
    <div class="modal">
        <div class="success-icon">🎉</div>
        <h2>Booking Confirmed!</h2>
        <p>Your trip to <strong><?php echo htmlspecialchars($destination['City'] ?? 'your destination'); ?></strong> has been booked successfully. Get ready for an amazing adventure!</p>
        <div class="modal-actions">
            <a href="my_bookings.php" class="btn-modal-primary">View My Bookings</a>
            <a href="browse_packages.php" class="btn-modal-secondary">Browse More</a>
        </div>
    </div>
</div>

<script>
const user = JSON.parse(localStorage.getItem('user'));
if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}
document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();

// Pre-fill user details
document.getElementById('fname').value  = user.name    || '';
document.getElementById('lname').value  = user.surname || '';
document.getElementById('email').value  = user.email   || '';

const pricePerPerson = <?php echo $package['Price']; ?>;

function updateTotal() {
    const num = parseInt(document.getElementById('num-travellers').value);
    const total = pricePerPerson * num;
    document.getElementById('num-display').textContent = num;
    document.getElementById('total-price').textContent =
        'R' + total.toLocaleString('en-ZA', { minimumFractionDigits: 2 });
}

async function confirmBooking() {
    const btn = document.getElementById('btn-confirm');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'BookPackage',
            apikey: user.apikey,
            traveller_id: user.traveller_id,
            package_id: <?php echo $package_id; ?>,
            num_travellers: document.getElementById('num-travellers').value,
            special_requests: document.getElementById('special-requests').value
        })
    });

    const result = await res.json();

    if (result.status === 'success') {
        document.getElementById('success-modal').classList.add('active');
    } else {
        alert(result.data || 'Booking failed. Please try again.');
        btn.disabled = false;
        btn.textContent = '🧳 Confirm Booking';
    }
}
</script>

</body>
</html>