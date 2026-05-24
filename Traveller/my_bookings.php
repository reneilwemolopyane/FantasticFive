<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | My Bookings</title>
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

        /* TABS */
        .tabs {
            display: flex; gap: 8px; margin-bottom: 24px;
        }
        .tab {
            padding: 10px 20px; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            border: 1.5px solid #e2e8f0; background: #fff;
            color: #64748b; transition: all 0.2s;
        }
        .tab.active {
            background: #2563eb; color: #fff; border-color: #2563eb;
        }
        .tab:hover:not(.active) { border-color: #94a3b8; color: #1e293b; }

        /* BOOKINGS LIST */
        .bookings-list { display: flex; flex-direction: column; gap: 16px; }
        .booking-card {
            background: #fff; border-radius: 14px; padding: 20px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            display: flex; align-items: center; gap: 20px;
            transition: box-shadow 0.2s;
        }
        .booking-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .booking-img {
            width: 100px; height: 80px; border-radius: 10px;
            object-fit: cover; flex-shrink: 0;
        }
        .booking-info { flex: 1; }
        .booking-info h3 { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .booking-info .agency { font-size: 13px; color: #64748b; margin-bottom: 8px; }
        .booking-meta { display: flex; gap: 16px; font-size: 13px; color: #64748b; }
        .booking-meta span { display: flex; align-items: center; gap: 4px; }
        .booking-right { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }
        .status-badge {
            padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
        }
        .status-confirmed { background: #dcfce7; color: #16a34a; }
        .status-pending   { background: #fef9c3; color: #ca8a04; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        .booking-price { font-size: 18px; font-weight: 800; color: #2563eb; }
        .booking-actions { display: flex; gap: 8px; }
        .btn-details {
            padding: 7px 14px; border: 1.5px solid #2dd4bf; color: #2dd4bf;
            border-radius: 6px; text-decoration: none;
            font-weight: 600; font-size: 13px; transition: all 0.2s;
        }
        .btn-details:hover { background: #2dd4bf; color: #fff; }
        .btn-review {
            padding: 7px 14px; background: #2563eb; color: #fff;
            border-radius: 6px; text-decoration: none;
            font-weight: 600; font-size: 13px; transition: background 0.2s;
            border: none; cursor: pointer;
        }
        .btn-review:hover { background: #1d4ed8; }

        /* EMPTY STATE */
        .empty-state { text-align: center; padding: 60px 20px; color: #94a3b8; }
        .empty-state .empty-icon { font-size: 52px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; margin-bottom: 16px; }
        .btn-primary {
            display: inline-block; padding: 10px 24px;
            background: #2563eb; color: #fff; border-radius: 8px;
            text-decoration: none; font-weight: 600; font-size: 14px;
        }
        .btn-primary:hover { background: #1d4ed8; }

        /* LOADING */
        .loading { text-align: center; padding: 40px; color: #94a3b8; font-size: 15px; }
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
        <a href="group_trips.php" class="nav-link"><span class="icon">👥</span> Group Trips</a>
        <a href="my_bookings.php" class="nav-link active"><span class="icon">🧳</span> My Bookings</a>
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
            <h2>🧳 My Bookings</h2>
            <p>View and manage your travel bookings</p>
        </div>
        <div class="avatar" id="avatar-initial">?</div>
    </div>

    <div class="page-body">

        <!-- TABS -->
        <div class="tabs">
            <div class="tab active" onclick="filterBookings('all', this)">All Bookings</div>
            <div class="tab" onclick="filterBookings('upcoming', this)">Upcoming</div>
            <div class="tab" onclick="filterBookings('past', this)">Past Trips</div>
        </div>

        <!-- BOOKINGS LIST -->
        <div class="bookings-list" id="bookings-list">
            <div class="loading">Loading your bookings...</div>
        </div>

    </div>
</div>

<script>
const user = JSON.parse(localStorage.getItem('user'));
if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}
document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();

const dest_images = {
    "Cape Town":  "Pictures/CPT_attraction.jpeg",
    "Paris":      "Pictures/Paris_attraction.jpeg",
    "Bali":       "Pictures/Bali_attraction.jpeg",
    "Dubai":      "Pictures/Dubai_attraction.jpeg",
    "Zanzibar":   "Pictures/Zanzibar_attraction.jpeg",
    "Tokyo":      "Pictures/Japan_package.jpeg",
    "Nairobi":    "Pictures/Safari_package.jpeg",
    "Port Louis": "Pictures/Mauritius_package.jpeg",
};
const fallback = "Pictures/Japan_package.jpeg";

let allBookings = [];
let currentFilter = 'all';

async function loadBookings() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'GetAllBookings',
            apikey: user.apikey,
            traveller_id: user.traveller_id
        })
    });
    const result = await res.json();
    if (result.status === 'success') {
        allBookings = result.data;
        renderBookings(allBookings);
    } else {
        document.getElementById('bookings-list').innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🧳</div>
                <p>You haven't booked any packages yet.</p>
                <a href="browse_packages.php" class="btn-primary">Browse Packages</a>
            </div>`;
    }
}

function filterBookings(filter, tab) {
    currentFilter = filter;
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');

    const today = new Date();
    let filtered = allBookings;

    if (filter === 'upcoming') {
        filtered = allBookings.filter(b => new Date(b.Start_date) >= today);
    } else if (filter === 'past') {
        filtered = allBookings.filter(b => new Date(b.End_date) < today);
    }

    renderBookings(filtered);
}

function renderBookings(bookings) {
    const list = document.getElementById('bookings-list');

    if (bookings.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">🧳</div>
                <p>No bookings found.</p>
                <a href="browse_packages.php" class="btn-primary">Browse Packages</a>
            </div>`;
        return;
    }

    const today = new Date();
    list.innerHTML = bookings.map(b => {
        const img = dest_images[b.City] || fallback;
        const startDate = new Date(b.Start_date);
        const endDate   = new Date(b.End_date);
        const isPast    = endDate < today;
        const statusClass = isPast ? 'status-confirmed' : 'status-confirmed';
        const statusLabel = isPast ? 'Completed' : 'Confirmed';

        return `
        <div class="booking-card">
            <img class="booking-img" src="${img}" alt="${b.Title}">
            <div class="booking-info">
                <h3>${b.Title}</h3>
                <p class="agency">🏢 ${b.Agency_name}</p>
                <div class="booking-meta">
                    <span>📅 ${b.Start_date}</span>
                    <span>→</span>
                    <span>${b.End_date}</span>
                    <span>⏱ ${b.Duration} Days</span>
                    ${b.City ? `<span>📍 ${b.City}</span>` : ''}
                </div>
            </div>
            <div class="booking-right">
                <span class="status-badge ${statusClass}">${statusLabel}</span>
                <span class="booking-price">R${parseFloat(b.Price).toLocaleString('en-ZA', {minimumFractionDigits: 2})}</span>
                <div class="booking-actions">
                    <a href="package_details.php?id=${b.PackageID}" class="btn-details">View Details</a>
                    ${isPast ? `<a href="my_reviews.php?package_id=${b.PackageID}" class="btn-review">⭐ Review</a>` : ''}
                </div>
            </div>
        </div>`;
    }).join('');
}

loadBookings();
</script>

</body>
</html>