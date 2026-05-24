<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tripistry | My Profile</title>
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
        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 28px;
            align-items: start;
        }

        /* PROFILE SIDEBAR CARD */
        .profile-card {
            background: #fff; border-radius: 14px; padding: 28px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            text-align: center; position: sticky; top: 80px;
        }
        .profile-avatar-large {
            width: 90px; height: 90px; background: #2dd4bf;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 36px; font-weight: 800;
            color: #fff; margin: 0 auto 16px;
        }
        .profile-name { font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .profile-email { font-size: 13px; color: #64748b; margin-bottom: 16px; }
        .profile-badge {
            display: inline-block; padding: 5px 14px;
            background: #eff6ff; color: #2563eb;
            border-radius: 20px; font-size: 12px; font-weight: 700;
            margin-bottom: 20px;
        }
        .profile-stats {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 10px; margin-top: 16px;
        }
        .profile-stat {
            background: #f8fafc; border-radius: 10px; padding: 12px;
        }
        .profile-stat h4 { font-size: 20px; font-weight: 800; color: #0f172a; }
        .profile-stat p  { font-size: 11px; color: #64748b; font-weight: 600; margin-top: 2px; }

        /* FORM CARDS */
        .form-card {
            background: #fff; border-radius: 14px; padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07); margin-bottom: 20px;
        }
        .form-card h3 {
            font-size: 16px; font-weight: 700; color: #0f172a;
            margin-bottom: 20px; padding-bottom: 10px;
            border-bottom: 2px solid #f1f5f9;
            display: flex; align-items: center; gap: 8px;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
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
        .form-group input[readonly] {
            background: #f1f5f9; color: #94a3b8; cursor: not-allowed;
        }
        .btn-save {
            padding: 11px 28px; background: #2563eb; color: #fff;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 700;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-save:hover { background: #1d4ed8; }
        .btn-save:disabled { background: #94a3b8; cursor: not-allowed; }

        /* PASSWORD STRENGTH */
        .password-strength { margin-top: 6px; height: 4px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
        .password-strength-fill { height: 100%; border-radius: 4px; width: 0; transition: all 0.3s; }
        .strength-weak   { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #22c55e; width: 100%; }
        .strength-label  { font-size: 11px; margin-top: 4px; color: #64748b; }

        /* DANGER ZONE */
        .danger-card {
            background: #fff; border-radius: 14px; padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            border: 1.5px solid #fee2e2;
        }
        .danger-card h3 {
            font-size: 16px; font-weight: 700; color: #dc2626;
            margin-bottom: 8px; display: flex; align-items: center; gap: 8px;
        }
        .danger-card p { font-size: 13px; color: #64748b; margin-bottom: 16px; }
        .btn-logout {
            padding: 11px 28px; background: #ef4444; color: #fff;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 700;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-logout:hover { background: #dc2626; }

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
        <a href="my_reviews.php" class="nav-link"><span class="icon">⭐</span> My Reviews</a>
        <a href="traveller_profile.php" class="nav-link active"><span class="icon">👤</span> Profile</a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php"><span>🚪</span> Logout</a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div>
            <h2>👤 My Profile</h2>
            <p>Manage your personal information</p>
        </div>
        <div class="avatar" id="avatar-initial">?</div>
    </div>

    <div class="page-body">
        <div class="profile-layout">

            <!-- LEFT — PROFILE CARD -->
            <div>
                <div class="profile-card">
                    <div class="profile-avatar-large" id="profile-avatar">?</div>
                    <div class="profile-name" id="profile-name">Loading...</div>
                    <div class="profile-email" id="profile-email">...</div>
                    <span class="profile-badge">✈️ Traveller</span>
                    <div class="profile-stats">
                        <div class="profile-stat">
                            <h4 id="ps-bookings">-</h4>
                            <p>Bookings</p>
                        </div>
                        <div class="profile-stat">
                            <h4 id="ps-reviews">-</h4>
                            <p>Reviews</p>
                        </div>
                        <div class="profile-stat">
                            <h4 id="ps-groups">-</h4>
                            <p>Group Trips</p>
                        </div>
                        <div class="profile-stat">
                            <h4>⭐</h4>
                            <p>Explorer</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT — FORMS -->
            <div>

                <!-- PERSONAL INFO -->
                <div class="form-card">
                    <h3>👤 Personal Information</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="fname" placeholder="First name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="lname" placeholder="Last name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="email" placeholder="Email">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" id="phone" placeholder="+27 ...">
                    </div>
                    <button class="btn-save" onclick="savePersonalInfo()">💾 Save Changes</button>
                </div>

                <!-- TRAVELLER DETAILS -->
                <div class="form-card">
                    <h3>🛂 Traveller Details</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" id="nationality" placeholder="e.g. South African">
                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select id="gender">
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Passport Number</label>
                            <input type="text" id="passport" placeholder="Passport number">
                        </div>
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" id="dob">
                        </div>
                    </div>
                    <button class="btn-save" onclick="saveTravellerDetails()">💾 Save Details</button>
                </div>

                <!-- CHANGE PASSWORD -->
                <div class="form-card">
                    <h3>🔒 Change Password</h3>
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" id="current-password" placeholder="Current password">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" id="new-password" placeholder="New password" oninput="checkStrength()">
                        <div class="password-strength">
                            <div class="password-strength-fill" id="strength-fill"></div>
                        </div>
                        <div class="strength-label" id="strength-label"></div>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" id="confirm-password" placeholder="Confirm new password">
                    </div>
                    <button class="btn-save" onclick="changePassword()">🔒 Update Password</button>
                </div>

                <!-- DANGER ZONE -->
                <div class="danger-card">
                    <h3>⚠️ Sign Out</h3>
                    <p>This will log you out of your Tripistry account on this device.</p>
                    <button class="btn-logout" onclick="logout()">🚪 Log Out</button>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const user = JSON.parse(localStorage.getItem('user'));
if (!user || user.user_type !== 'Traveller') {
    window.location.href = 'login.php';
}

// Populate UI from localStorage
document.getElementById('avatar-initial').textContent  = user.name.charAt(0).toUpperCase();
document.getElementById('profile-avatar').textContent  = user.name.charAt(0).toUpperCase();
document.getElementById('profile-name').textContent    = user.name + ' ' + user.surname;
document.getElementById('profile-email').textContent   = user.email;

// Pre-fill forms
document.getElementById('fname').value       = user.name    || '';
document.getElementById('lname').value       = user.surname || '';
document.getElementById('email').value       = user.email   || '';
document.getElementById('nationality').value = user.nationality || '';
document.getElementById('gender').value      = user.gender  || '';
document.getElementById('dob').value         = user.date_of_birth || '';

// Load stats
async function loadStats() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'GetTravellerStats',
            apikey: user.apikey,
            traveller_id: user.traveller_id
        })
    });
    const result = await res.json();
    if (result.status === 'success') {
        document.getElementById('ps-bookings').textContent = result.data.bookings ?? 0;
        document.getElementById('ps-reviews').textContent  = result.data.reviews  ?? 0;
        document.getElementById('ps-groups').textContent   = result.data.groups   ?? 0;
    }
}

// Save personal info
async function savePersonalInfo() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'UpdateProfile',
            apikey: user.apikey,
            user_id: user.user_id,
            name: document.getElementById('fname').value.trim(),
            surname: document.getElementById('lname').value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim()
        })
    });
    const result = await res.json();
    if (result.status === 'success') {
        // Update localStorage
        user.name    = document.getElementById('fname').value.trim();
        user.surname = document.getElementById('lname').value.trim();
        user.email   = document.getElementById('email').value.trim();
        localStorage.setItem('user', JSON.stringify(user));
        document.getElementById('profile-name').textContent   = user.name + ' ' + user.surname;
        document.getElementById('avatar-initial').textContent = user.name.charAt(0).toUpperCase();
        document.getElementById('profile-avatar').textContent = user.name.charAt(0).toUpperCase();
        showToast('Personal info updated!', 'success');
    } else {
        showToast(result.data || 'Update failed.', 'error');
    }
}

// Save traveller details
async function saveTravellerDetails() {
    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'UpdateTravellerDetails',
            apikey: user.apikey,
            traveller_id: user.traveller_id,
            nationality: document.getElementById('nationality').value.trim(),
            gender: document.getElementById('gender').value,
            passport_no: document.getElementById('passport').value.trim(),
            date_of_birth: document.getElementById('dob').value
        })
    });
    const result = await res.json();
    if (result.status === 'success') {
        user.nationality    = document.getElementById('nationality').value.trim();
        user.gender         = document.getElementById('gender').value;
        user.date_of_birth  = document.getElementById('dob').value;
        localStorage.setItem('user', JSON.stringify(user));
        showToast('Traveller details updated!', 'success');
    } else {
        showToast(result.data || 'Update failed.', 'error');
    }
}

// Change password
async function changePassword() {
    const current = document.getElementById('current-password').value;
    const newPass  = document.getElementById('new-password').value;
    const confirm  = document.getElementById('confirm-password').value;

    if (!current || !newPass || !confirm) { showToast('Please fill in all password fields.', 'error'); return; }
    if (newPass !== confirm) { showToast('New passwords do not match.', 'error'); return; }
    if (newPass.length < 9)  { showToast('Password must be at least 9 characters.', 'error'); return; }

    const res = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            type: 'ChangePassword',
            apikey: user.apikey,
            user_id: user.user_id,
            current_password: current,
            new_password: newPass
        })
    });
    const result = await res.json();
    if (result.status === 'success') {
        document.getElementById('current-password').value = '';
        document.getElementById('new-password').value     = '';
        document.getElementById('confirm-password').value = '';
        document.getElementById('strength-fill').className = 'password-strength-fill';
        document.getElementById('strength-label').textContent = '';
        showToast('Password updated successfully!', 'success');
    } else {
        showToast(result.data || 'Password update failed.', 'error');
    }
}

// Password strength checker
function checkStrength() {
    const val  = document.getElementById('new-password').value;
    const fill = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{9,}$/.test(val);
    const medium = val.length >= 6;

    if (strong) {
        fill.className = 'password-strength-fill strength-strong';
        label.textContent = '✅ Strong password';
        label.style.color = '#22c55e';
    } else if (medium) {
        fill.className = 'password-strength-fill strength-medium';
        label.textContent = '⚠️ Medium — add symbols, numbers and capitals';
        label.style.color = '#f59e0b';
    } else {
        fill.className = 'password-strength-fill strength-weak';
        label.textContent = '❌ Too weak';
        label.style.color = '#ef4444';
    }
}

function logout() {
    localStorage.removeItem('user');
    window.location.href = 'login.php';
}

function showToast(msg, type) {
    const toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast ' + type;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3500);
}

loadStats();
</script>

</body>
</html>