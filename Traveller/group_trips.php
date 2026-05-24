<?php
require_once 'config.php';

// Fetch all group trips with agency info and member count
$stmt = $connection->prepare("
    SELECT gt.*, a.Agency_name, a.Average_rating,
           COUNT(tgt.TravellerID) AS members_joined
    FROM GROUP_TRIP gt
    JOIN AGENCY a ON gt.AgencyID = a.AgencyID
    LEFT JOIN Traveller_group_trip tgt ON gt.GroupTripID = tgt.GroupTripID
    GROUP BY gt.GroupTripID
    ORDER BY gt.GroupTripID DESC
");
$stmt->execute();
$group_trips = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | Group Trips</title>
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
        .avatar {
            width: 38px; height: 38px; background: #2dd4bf;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-weight: 700; font-size: 15px; color: #fff;
        }
        .page-body { padding: 32px; flex: 1; }
        .results-header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;
        }
        .results-header h3 { font-size: 17px; font-weight: 700; color: #0f172a; }
        .results-header span { font-size: 13px; color: #64748b; }

        /* GRID */
        .trips-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .trip-card {
            background: #fff; border-radius: 14px; padding: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex; flex-direction: column; gap: 14px;
        }
        .trip-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }

        .trip-header { display: flex; align-items: center; justify-content: space-between; }
        .trip-icon {
            width: 48px; height: 48px; background: #eff6ff;
            border-radius: 12px; display: flex; align-items: center;
            justify-content: center; font-size: 22px;
        }
        .seats-badge {
            padding: 5px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 700;
        }
        .seats-available { background: #dcfce7; color: #16a34a; }
        .seats-full      { background: #fee2e2; color: #dc2626; }
        .seats-almost    { background: #fef9c3; color: #ca8a04; }

        .trip-title { font-size: 17px; font-weight: 700; color: #0f172a; }
        .trip-agency { font-size: 13px; color: #64748b; margin-top: 2px; }

        /* PROGRESS BAR */
        .progress-wrapper { }
        .progress-label {
            display: flex; justify-content: space-between;
            font-size: 12px; color: #64748b; margin-bottom: 6px;
        }
        .progress-bar {
            width: 100%; height: 8px; background: #e2e8f0;
            border-radius: 20px; overflow: hidden;
        }
        .progress-fill {
            height: 100%; border-radius: 20px;
            background: #2dd4bf; transition: width 0.4s ease;
        }
        .progress-fill.almost { background: #f59e0b; }
        .progress-fill.full   { background: #ef4444; }

        .trip-meta { display: flex; gap: 10px; flex-wrap: wrap; }
        .meta-tag {
            background: #f8fafc; border-radius: 6px;
            padding: 5px 10px; font-size: 12px; color: #64748b;
        }

        .btn-join {
            width: 100%; padding: 11px; background: #2563eb; color: #fff;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-join:hover { background: #1d4ed8; }
        .btn-join:disabled {
            background: #e2e8f0; color: #94a3b8; cursor: not-allowed;
        }
        .btn-joined {
            width: 100%; padding: 11px; background: #f0fdfa;
            color: #2dd4bf; border: 1.5px solid #2dd4bf;
            border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: default;
        }

        /* EMPTY STATE */
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state .empty-icon { font-size: 52px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; }

        /* TOAST */
        .toast {
            position: fixed; bottom: 32px; right: 32px;
            background: #1e293b; color: #fff;
            padding: 14px 20px; border-radius: 10px;
            font-size: 14px; font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            display: none; z-index: 999;
            animation: slideIn 0.3s ease;
        }
        .toast.success { border-left: 4px solid #2dd4bf; }
        .toast.error   { border-left: 4px solid #ef4444; }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
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
        <a href="browse_packages.php" class="nav-link"><span class="icon">📦</span> Browse Packages</a>
        <a href="group_trips.php" class="nav-link active"><span class="icon">👥</span> Group Trips</a>
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
            <h2>👥 Group Trips</h2>
            <p>Join a group trip and travel with others</p>
        </div>
        <div class="avatar" id="avatar-initial">?</div>
    </div>

    <div class="page-body">

        <div class="results-header">
            <h3>Available Group Trips</h3>
            <span><?php echo count($group_trips); ?> trip<?php echo count($group_trips) !== 1 ? 's' : ''; ?> available</span>
        </div>

        <?php if (empty($group_trips)): ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <p>No group trips available at the moment. Check back soon!</p>
        </div>
        <?php else: ?>
        <div class="trips-grid">
            <?php foreach ($group_trips as $trip):
                $members   = (int)$trip['members_joined'];
                $limit     = (int)$trip['Group_size_limit'];
                $seats_left = $limit - $members;
                $pct       = $limit > 0 ? round(($members / $limit) * 100) : 0;
                $is_full   = $seats_left <= 0;
                $is_almost = !$is_full && $pct >= 75;

                if ($is_full)        $badge_class = 'seats-full';
                elseif ($is_almost)  $badge_class = 'seats-almost';
                else                 $badge_class = 'seats-available';

                if ($is_full)        $fill_class = 'full';
                elseif ($is_almost)  $fill_class = 'almost';
                else                 $fill_class = '';
            ?>
            <div class="trip-card">
                <div class="trip-header">
                    <div class="trip-icon">👥</div>
                    <span class="seats-badge <?php echo $badge_class; ?>">
                        <?php echo $is_full ? 'Full' : $seats_left . ' seats left'; ?>
                    </span>
                </div>

                <div>
                    <div class="trip-title">Group Trip #<?php echo $trip['GroupTripID']; ?></div>
                    <div class="trip-agency">by <?php echo htmlspecialchars($trip['Agency_name']); ?> · ⭐ <?php echo number_format($trip['Average_rating'],1); ?></div>
                </div>

                <div class="progress-wrapper">
                    <div class="progress-label">
                        <span><?php echo $members; ?> / <?php echo $limit; ?> members</span>
                        <span><?php echo $pct; ?>% full</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $fill_class; ?>" style="width: <?php echo $pct; ?>%"></div>
                    </div>
                </div>

                <div class="trip-meta">
                    <span class="meta-tag">👥 Max <?php echo $limit; ?> people</span>
                    <span class="meta-tag">🏢 <?php echo htmlspecialchars($trip['Agency_name']); ?></span>
                </div>

                <button
                    class="btn-join"
                    id="btn-<?php echo $trip['GroupTripID']; ?>"
                    onclick="joinTrip(<?php echo $trip['GroupTripID']; ?>, this)"
                    <?php echo $is_full ? 'disabled' : ''; ?>>
                    <?php echo $is_full ? '🚫 Trip Full' : '➕ Join Trip'; ?>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
const user = JSON.parse(localStorage.getItem('user'));
if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}
document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();

// Check which trips this traveller has already joined
async function checkJoinedTrips() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'GetJoinedTrips',
            apikey: user.apikey,
            traveller_id: user.traveller_id
        })
    });
    const result = await res.json();
    if (result.status === 'success') {
        result.data.forEach(tripId => {
            const btn = document.getElementById('btn-' + tripId);
            if (btn) {
                btn.outerHTML = `<button class="btn-joined">✅ Already Joined</button>`;
            }
        });
    }
}

async function joinTrip(groupTripId, btn) {
    btn.disabled = true;
    btn.textContent = 'Joining...';

    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'JoinGroupTrip',
            apikey: user.apikey,
            traveller_id: user.traveller_id,
            group_trip_id: groupTripId
        })
    });
    const result = await res.json();

    if (result.status === 'success') {
        btn.outerHTML = `<button class="btn-joined">✅ Already Joined</button>`;
        showToast('Successfully joined the group trip!', 'success');
    } else {
        btn.disabled = false;
        btn.textContent = '➕ Join Trip';
        showToast(result.data || 'Failed to join trip.', 'error');
    }
}

function showToast(msg, type) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast ' + type;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

checkJoinedTrips();
</script>

</body>
</html>