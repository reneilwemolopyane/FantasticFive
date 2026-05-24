<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | My Reviews</title>
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

        /* LAYOUT */
        .reviews-layout {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 28px;
        }

        /* SECTION HEADER */
        .section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px;
        }
        .section-header h3 { font-size: 17px; font-weight: 700; color: #0f172a; }
        .section-header span { font-size: 13px; color: #64748b; }

        /* REVIEW CARDS */
        .review-card {
            background: #fff; border-radius: 14px; padding: 20px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 16px;
            transition: box-shadow 0.2s;
        }
        .review-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .review-card-header {
            display: flex; align-items: center;
            justify-content: space-between; margin-bottom: 12px;
        }
        .review-package h4 { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
        .review-package p  { font-size: 12px; color: #64748b; }
        .review-stars { color: #f59e0b; font-size: 18px; letter-spacing: 2px; }
        .review-comment {
            font-size: 14px; color: #475569; line-height: 1.6;
            background: #f8fafc; border-radius: 8px; padding: 12px 14px;
            margin-bottom: 10px;
        }
        .review-date { font-size: 12px; color: #94a3b8; }

        /* WRITE REVIEW FORM */
        .form-card {
            background: #fff; border-radius: 14px; padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            position: sticky; top: 80px;
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
        .form-group select,
        .form-group textarea {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; color: #1e293b; font-family: inherit;
            outline: none; background: #f8fafc; transition: border-color 0.2s;
        }
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
            background: #fff;
        }
        .form-group textarea { resize: vertical; min-height: 100px; }

        /* STAR RATING SELECTOR */
        .star-selector {
            display: flex; flex-direction: row-reverse;
            justify-content: flex-end; gap: 4px; margin-bottom: 4px;
        }
        .star-selector input { display: none; }
        .star-selector label {
            font-size: 28px; color: #e2e8f0; cursor: pointer;
            transition: color 0.15s; margin-bottom: 0;
        }
        .star-selector label:hover,
        .star-selector label:hover ~ label,
        .star-selector input:checked ~ label {
            color: #f59e0b;
        }

        .btn-submit {
            width: 100%; padding: 12px; background: #2563eb; color: #fff;
            border: none; border-radius: 8px; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: background 0.2s; margin-top: 4px;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .btn-submit:disabled { background: #94a3b8; cursor: not-allowed; }

        /* EMPTY STATE */
        .empty-state { text-align: center; padding: 48px 20px; color: #94a3b8; }
        .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 15px; }

        /* TOAST */
        .toast {
            position: fixed; bottom: 32px; right: 32px;
            background: #1e293b; color: #fff;
            padding: 14px 20px; border-radius: 10px;
            font-size: 14px; font-weight: 500;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            display: none; z-index: 999;
        }
        .toast.success { border-left: 4px solid #2dd4bf; }
        .toast.error   { border-left: 4px solid #ef4444; }
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
        <a href="my_bookings.php" class="nav-link"><span class="icon">🧳</span> My Bookings</a>
        <a href="my_reviews.php" class="nav-link active"><span class="icon">⭐</span> My Reviews</a>
        <a href="traveller_profile.php" class="nav-link"><span class="icon">👤</span> Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php"><span>🚪</span> Logout</a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div>
            <h2>⭐ My Reviews</h2>
            <p>Share your travel experiences</p>
        </div>
        <div class="avatar" id="avatar-initial">?</div>
    </div>

    <div class="page-body">
        <div class="reviews-layout">

            <!-- LEFT — MY REVIEWS LIST -->
            <div>
                <div class="section-header">
                    <h3>Your Reviews</h3>
                    <span id="review-count">Loading...</span>
                </div>
                <div id="reviews-list">
                    <div class="empty-state">
                        <div class="empty-icon">⭐</div>
                        <p>Loading your reviews...</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT — WRITE REVIEW FORM -->
            <div>
                <div class="form-card">
                    <h3>✍️ Write a Review</h3>

                    <div class="form-group">
                        <label>Select Package</label>
                        <select id="package-select">
                            <option value="">Loading your bookings...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Your Rating</label>
                        <div class="star-selector">
                            <input type="radio" name="rating" id="s5" value="5">
                            <label for="s5">★</label>
                            <input type="radio" name="rating" id="s4" value="4">
                            <label for="s4">★</label>
                            <input type="radio" name="rating" id="s3" value="3">
                            <label for="s3">★</label>
                            <input type="radio" name="rating" id="s2" value="2">
                            <label for="s2">★</label>
                            <input type="radio" name="rating" id="s1" value="1">
                            <label for="s1">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Your Comment</label>
                        <textarea id="comment" placeholder="Share your experience with this package..."></textarea>
                    </div>

                    <button class="btn-submit" id="btn-submit" onclick="submitReview()">
                        ⭐ Submit Review
                    </button>
                </div>
            </div>

        </div>
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

// Check if came from a specific package
const urlParams = new URLSearchParams(window.location.search);
const preselectedPackageId = urlParams.get('package_id');

// Load booked packages for the dropdown
async function loadBookedPackages() {
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
    const select = document.getElementById('package-select');
    select.innerHTML = '<option value="">-- Select a package --</option>';

    if (result.status === 'success' && result.data.length > 0) {
        result.data.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.PackageID;
            opt.textContent = b.Title + ' (' + b.Agency_name + ')';
            if (preselectedPackageId && b.PackageID == preselectedPackageId) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    } else {
        select.innerHTML = '<option value="">No bookings found — book a package first</option>';
    }
}

// Load existing reviews
async function loadReviews() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'GetMyReviews',
            apikey: user.apikey,
            traveller_id: user.traveller_id
        })
    });
    const result = await res.json();
    const list = document.getElementById('reviews-list');
    const countEl = document.getElementById('review-count');

    if (result.status === 'success' && result.data.length > 0) {
        countEl.textContent = result.data.length + ' review' + (result.data.length !== 1 ? 's' : '');
        list.innerHTML = result.data.map(r => `
            <div class="review-card">
                <div class="review-card-header">
                    <div class="review-package">
                        <h4>${r.Title}</h4>
                        <p>🏢 ${r.Agency_name}</p>
                    </div>
                    <div class="review-stars">${'★'.repeat(r.Rating)}${'☆'.repeat(5 - r.Rating)}</div>
                </div>
                <div class="review-comment">"${r.Comment}"</div>
                <div class="review-date">📅 Reviewed on ${r.Review_date}</div>
            </div>
        `).join('');
    } else {
        countEl.textContent = '0 reviews';
        list.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">⭐</div>
                <p>You haven't written any reviews yet.<br>Book a package and share your experience!</p>
            </div>`;
    }
}

// Submit review
async function submitReview() {
    const packageId = document.getElementById('package-select').value;
    const rating    = document.querySelector('input[name="rating"]:checked');
    const comment   = document.getElementById('comment').value.trim();

    if (!packageId) { showToast('Please select a package.', 'error'); return; }
    if (!rating)    { showToast('Please select a rating.', 'error'); return; }
    if (!comment)   { showToast('Please write a comment.', 'error'); return; }

    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Submitting...';

    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'SubmitReview',
            apikey: user.apikey,
            traveller_id: user.traveller_id,
            package_id: packageId,
            rating: rating.value,
            comment: comment
        })
    });

    const result = await res.json();

    if (result.status === 'success') {
        showToast('Review submitted successfully!', 'success');
        document.getElementById('comment').value = '';
        document.querySelectorAll('input[name="rating"]').forEach(r => r.checked = false);
        document.getElementById('package-select').value = '';
        loadReviews();
    } else {
        showToast(result.data || 'Failed to submit review.', 'error');
    }

    btn.disabled = false;
    btn.textContent = '⭐ Submit Review';
}

function showToast(msg, type) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast ' + type;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

loadBookedPackages();
loadReviews();
</script>

</body>
</html>