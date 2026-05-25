<?php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | Browse Packages</title>

    <style>

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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
            left: 0;
            top: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            text-decoration: none;
        }

        .sidebar-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .sidebar-brand span {
            color: white;
            font-size: 18px;
            font-weight: 700;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            padding: 20px 0;
        }

        .nav-link {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .nav-link.active {
            color: #2dd4bf;
            border-left: 3px solid #2dd4bf;
            background: rgba(45,212,191,0.08);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .sidebar-footer a {
            text-decoration: none;
            color: #ef4444;
            font-weight: 600;
        }

        .main {
            margin-left: 240px;
            flex: 1;
        }

        .topbar {
            background: white;
            padding: 20px 32px;
            border-bottom: 1px solid #e2e8f0;
        }

        .topbar h2 {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .topbar p {
            color: #64748b;
            font-size: 14px;
        }

        .page-body {
            padding: 32px;
        }

        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 160px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 14px;
        }

        .btn-filter {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            height: 42px;
            align-self: flex-end;
        }

        .btn-filter:hover {
            background: #1d4ed8;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .package-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }

        .package-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-body {
            padding: 16px;
        }

        .card-body h4 {
            margin-bottom: 8px;
            font-size: 18px;
        }

        .card-destination {
            color: #64748b;
            margin-bottom: 10px;
        }

        .card-meta {
            margin-bottom: 12px;
            font-size: 14px;
            color: #64748b;
        }

        .price {
            color: #2563eb;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .agency-name {
            margin-bottom: 14px;
            color: #64748b;
            font-size: 13px;
        }

        .card-actions {
            display: flex;
            gap: 10px;
        }

        .btn-details,
        .btn-book {
            flex: 1;
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .btn-details {
            border: 1px solid #2dd4bf;
            color: #2dd4bf;
        }

        .btn-book {
            background: #2563eb;
            color: white;
        }

        .empty-state {
            grid-column: 1/-1;
            background: white;
            padding: 60px;
            text-align: center;
            border-radius: 12px;
        }

    </style>
</head>

<body>

<aside class="sidebar">

    <a href="traveller_dashboard.php" class="sidebar-brand">
        <img src="Pictures/Tripistry_logo.jpg">
        <span>Tripistry</span>
    </a>

    <nav class="sidebar-nav">

        <a href="traveller_dashboard.php" class="nav-link">
            🏠 Dashboard
        </a>

        <a href="browse_destinations.php" class="nav-link">
            🌍 Destinations
        </a>

        <a href="browse_packages.php" class="nav-link active">
            📦 Browse Packages
        </a>

        <a href="group_trips.php" class="nav-link">
            👥 Group Trips
        </a>

        <a href="my_bookings.php" class="nav-link">
            🧳 My Bookings
        </a>

        <a href="my_reviews.php" class="nav-link">
            ⭐ My Reviews
        </a>

        <a href="traveller_profile.php" class="nav-link">
            👤 Profile
        </a>

    </nav>

    <div class="sidebar-footer">
        <a href="logout.php">🚪 Logout</a>
    </div>

</aside>

<div class="main">

    <div class="topbar">
        <h2>📦 Browse Packages</h2>
        <p>Find and compare travel packages</p>
    </div>

    <div class="page-body">

        <form class="filter-bar" id="filter-form">

            <div class="filter-group">
                <label>Destination</label>
                <input type="text" id="destination" placeholder="City or country">
            </div>

            <div class="filter-group">
                <label>Package Type</label>

                <select id="type">

                    <option value="">All Types</option>

                    <option value="Adventure">Adventure</option>
                    <option value="Beach">Beach</option>
                    <option value="Cultural">Cultural</option>
                    <option value="Family">Family</option>
                    <option value="Luxury">Luxury</option>
                    <option value="Budget">Budget</option>

                </select>
            </div>

            <div class="filter-group">
                <label>Min Price</label>
                <input type="number" id="min">
            </div>

            <div class="filter-group">
                <label>Max Price</label>
                <input type="number" id="max">
            </div>

            <div class="filter-group">
                <label>Sort By</label>

                <select id="sort">

                    <option value="rating">Top Rated</option>
                    <option value="price_asc">Price Low → High</option>
                    <option value="price_desc">Price High → Low</option>
                    <option value="duration">Duration</option>

                </select>
            </div>

            <button class="btn-filter">
                Search
            </button>

        </form>

        <div class="results-header">

            <h3>Available Packages</h3>

            <span id="results-count">
                0 packages found
            </span>

        </div>

        <div class="packages-grid" id="packages-grid">

        </div>

    </div>

</div>

<script>

const user = JSON.parse(localStorage.getItem('user'));

if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}

const imageMap = {

    "Cape Town": "CPT_attraction.jpeg",
    "Paris": "Paris_attraction.jpeg",
    "Bali": "Bali_attraction.jpeg",
    "Tokyo": "Japan_package.jpeg",
    "Nairobi": "Safari_package.jpeg",
    "Port Louis": "Mauritius_package.jpeg",
    "Zanzibar": "Zanzibar_attraction.jpeg"
};

async function loadPackages() {

    const destination = document.getElementById('destination').value;
    const type = document.getElementById('type').value;
    const min = document.getElementById('min').value;
    const max = document.getElementById('max').value;
    const sort = document.getElementById('sort').value;

    const response = await fetch(
        `api/search_packages.php?destination=${destination}&type=${type}&min=${min}&max=${max}&sort=${sort}`
    );

    const data = await response.json();

    const grid = document.getElementById('packages-grid');

    const resultsCount = document.getElementById('results-count');

    resultsCount.textContent =
        `${data.count} package${data.count !== 1 ? 's' : ''} found`;

    grid.innerHTML = '';

    if (data.packages.length === 0) {

        grid.innerHTML = `
            <div class="empty-state">
                <h3>No packages found</h3>
            </div>
        `;

        return;
    }

    data.packages.forEach(pkg => {

        const image =
            imageMap[pkg.City] || "Japan_package.jpeg";

        grid.innerHTML += `

        <div class="package-card">

            <img src="Pictures/${image}">

            <div class="card-body">

                <h4>${pkg.Title}</h4>

                <p class="card-destination">
                    📍 ${pkg.City}, ${pkg.Country}
                </p>

                <div class="card-meta">
                    ⏱ ${pkg.Duration} Days
                </div>

                <p class="price">
                    R${Number(pkg.Price).toLocaleString()}
                </p>

                <p class="agency-name">
                    by ${pkg.Agency_name}
                </p>

                <div class="card-actions">

                    <a
                        href="package_details.php?id=${pkg.PackageID}"
                        class="btn-details"
                    >
                        Details
                    </a>

                    <a
                        href="book_package.php?id=${pkg.PackageID}"
                        class="btn-book"
                    >
                        Book Now
                    </a>

                </div>

            </div>

        </div>
        `;
    });
}

document
    .getElementById('filter-form')
    .addEventListener('submit', function(e) {

        e.preventDefault();

        loadPackages();
    });

loadPackages();

</script>

</body>
</html>