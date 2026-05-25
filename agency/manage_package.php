<?php include 'agency_header.php'; ?>
<link rel="stylesheet" href="booking.css">

<div class="management-view-container">
  <div class="section-header-flex">
    <div>
      <h1>Active Catalog Management</h1>
      <p class="subtitle">Modify live parameters or temporarily toggle item visibility settings.</p>
    </div>
    <a href="create_package.php" class="btn-primary-action">+ Build New Package</a>
  </div>

  <!-- Search / filter bar -->
  <div class="filter-bar" style="margin-bottom:1.2rem; display:flex; gap:.75rem; flex-wrap:wrap; align-items:center;">
    <input type="text" id="pkg-search" placeholder="Search by title or destination…"
           style="padding:.5rem .85rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; flex:1; min-width:200px;">
    <select id="pkg-filter-type" style="padding:.5rem .85rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem;">
      <option value="">All Types</option>
      <option value="Luxury">Luxury</option>
      <option value="Adventure">Adventure</option>
      <option value="Cultural">Cultural</option>
      <option value="Beach">Beach</option>
      <option value="Family">Family</option>
      <option value="Budget">Budget</option>
      <option value="Leisure">Leisure</option>
    </select>
    <select id="pkg-filter-status" style="padding:.5rem .85rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem;">
      <option value="">All Statuses</option>
      <option value="Active">Active</option>
      <option value="Delisted">Delisted</option>
    </select>
  </div>

  <div class="table-responsive-wrapper">
    <table class="dashboard-data-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Package Identity</th>
          <th>Destination</th>
          <th>Type</th>
          <th>Price Structure</th>
          <th>Duration Scale</th>
          <th>Dates</th>
          <th>Capacity</th>
          <th>Live Status</th>
          <th class="text-center">Operations Options</th>
        </tr>
      </thead>
      <tbody id="packages-table-body">
        <tr>
          <td colspan="10" style="text-align:center; color:#64748b; padding:2rem;">
            Loading active catalog items directly from the database…
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <p id="pkg-empty-msg" style="display:none; text-align:center; color:#64748b; margin-top:1rem;">
    No packages match your current filters.
  </p>
</div>

<!-- ── Edit Price Modal ─────────────────────────────────────────────────── -->
<div id="price-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:14px; padding:2rem; width:360px; box-shadow:0 8px 32px rgba(0,0,0,.18);">
    <h3 style="margin:0 0 .5rem;">Update Package Price</h3>
    <p style="color:#64748b; font-size:.9rem; margin:0 0 1.2rem;">
      <strong id="modal-pkg-title"></strong>
    </p>
    <label style="font-size:.9rem; font-weight:600; display:block; margin-bottom:.4rem;">New Price (ZAR)</label>
    <input type="number" id="modal-price-input" min="1" step="0.01"
           style="width:100%; padding:.6rem .8rem; border:1px solid #cbd5e1; border-radius:8px; font-size:1rem; box-sizing:border-box;">
    <div style="display:flex; gap:.75rem; margin-top:1.2rem; justify-content:flex-end;">
      <button onclick="closePriceModal()" style="padding:.55rem 1.2rem; border:1px solid #cbd5e1; border-radius:8px; background:#f8fafc; cursor:pointer;">Cancel</button>
      <button onclick="submitPriceUpdate()" style="padding:.55rem 1.4rem; border:none; border-radius:8px; background:#3b82f6; color:#fff; font-weight:600; cursor:pointer;">Save Price</button>
    </div>
  </div>
</div>

<script>
// ── State ────────────────────────────────────────────────────────────────────
let allPackages = [];
let activePriceId = null;

// ── Bootstrap ────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  loadPackages();

  document.getElementById("pkg-search").addEventListener("input", renderTable);
  document.getElementById("pkg-filter-type").addEventListener("change", renderTable);
  document.getElementById("pkg-filter-status").addEventListener("change", renderTable);
});

// ── Fetch from API ────────────────────────────────────────────────────────────
async function loadPackages() {
  const data = await transmitAgencyRequest("api.php", { type: "GetAllPackages" });
  if (!data) return;
  allPackages = data;
  renderTable();
}

// ── Filter + render ───────────────────────────────────────────────────────────
function renderTable() {
  const search     = document.getElementById("pkg-search").value.toLowerCase();
  const typeFilter = document.getElementById("pkg-filter-type").value;
  const statusFilter = document.getElementById("pkg-filter-status").value;

  const filtered = allPackages.filter(pkg => {
    const matchText   = pkg.title.toLowerCase().includes(search) || (pkg.destination || "").toLowerCase().includes(search);
    const matchType   = !typeFilter   || pkg.pack_type === typeFilter;
    const matchStatus = !statusFilter || pkg.status === statusFilter;
    return matchText && matchType && matchStatus;
  });

  const tbody = document.getElementById("packages-table-body");
  const emptyMsg = document.getElementById("pkg-empty-msg");

  if (filtered.length === 0) {
    tbody.innerHTML = "";
    emptyMsg.style.display = "block";
    return;
  }
  emptyMsg.style.display = "none";

  tbody.innerHTML = filtered.map(pkg => {
    const imgSrc    = pkg.image_url ? pkg.image_url : "../frontend/Japan_package.jpeg";
    const isDelisted = pkg.status === "Delisted";
    const badgeStyle = isDelisted
      ? "background:#fee2e2; color:#dc2626;"
      : "background:#dcfce7; color:#16a34a;";
    const toggleLabel = isDelisted ? "Re-list" : "Delist";

    return `
      <tr id="package-row-${pkg.id}">
        <td><div class="thumb-crop"><img src="${imgSrc}" alt="${escHtml(pkg.title)}"></div></td>
        <td><strong>${escHtml(pkg.title)}</strong></td>
        <td>${escHtml(pkg.destination || '—')}</td>
        <td><span style="font-size:.82rem; background:#eff6ff; color:#3b82f6; padding:.2rem .6rem; border-radius:20px; font-weight:600;">${escHtml(pkg.pack_type || '—')}</span></td>
        <td class="monospaced-currency">R${fmtMoney(pkg.price)}</td>
        <td>${pkg.duration} days</td>
        <td style="font-size:.83rem; color:#475569;">${pkg.start_date || '—'}<br>${pkg.end_date || '—'}</td>
        <td style="text-align:center;">${pkg.max_people}</td>
        <td><span id="status-label-${pkg.id}" class="status-badge" style="${badgeStyle}">${pkg.status}</span></td>
        <td>
          <div class="action-btn-cluster">
            <button class="btn-action edit"   onclick="openPriceModal(${pkg.id}, '${escHtml(pkg.title)}', ${pkg.price})">Update Price</button>
            <button class="btn-action toggle" onclick="toggleVisibility(${pkg.id})">${toggleLabel}</button>
            <button class="btn-action delete" onclick="deletePackage(${pkg.id})">Delete</button>
          </div>
        </td>
      </tr>
    `;
  }).join("");
}

// ── Price Modal ───────────────────────────────────────────────────────────────
function openPriceModal(id, title, currentPrice) {
  activePriceId = id;
  document.getElementById("modal-pkg-title").textContent = title;
  document.getElementById("modal-price-input").value = currentPrice;
  document.getElementById("price-modal").style.display = "flex";
}

function closePriceModal() {
  document.getElementById("price-modal").style.display = "none";
  activePriceId = null;
}

async function submitPriceUpdate() {
  const newPrice = parseFloat(document.getElementById("modal-price-input").value);
  if (!newPrice || newPrice <= 0) { alert("Enter a valid positive price."); return; }

  const res = await transmitAgencyRequest("api.php", {
    type: "UpdatePackagePrice",
    id: activePriceId,
    price: newPrice
  });

  if (res) {
    // Update local state and re-render (no page reload)
    const pkg = allPackages.find(p => p.id === activePriceId);
    if (pkg) pkg.price = newPrice;
    closePriceModal();
    renderTable();
  }
}

// ── Toggle Visibility ─────────────────────────────────────────────────────────
async function toggleVisibility(packageId) {
  const res = await transmitAgencyRequest("api.php", {
    type: "TogglePackageVisibility",
    id: packageId
  });

  if (res) {
    const pkg = allPackages.find(p => p.id === packageId);
    if (pkg) pkg.status = res.new_status;
    renderTable();
  }
}

// ── Delete ────────────────────────────────────────────────────────────────────
async function deletePackage(packageId) {
  const pkg = allPackages.find(p => p.id === packageId);
  if (!confirm(`Permanently delete "${pkg?.title || 'this package'}" from the database?`)) return;

  const res = await transmitAgencyRequest("api.php", {
    type: "DeletePackage",
    id: packageId
  });

  if (res) {
    allPackages = allPackages.filter(p => p.id !== packageId);
    renderTable();
  }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmtMoney(v) {
  return parseFloat(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escHtml(str) {
  return String(str).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
}

async function transmitAgencyRequest(url, payload) {
  try {
    const token = sessionStorage.getItem("api_key");
    if (token && !payload.api_key) payload.api_key = token;

    const response = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const json = await response.json();
    if (json.status === "success") return json.data;
    alert("Error: " + json.data);
    return null;
  } catch (err) {
    console.error("Network error:", err);
    alert("Unable to reach server.");
    return null;
  }
}
</script>

<?php include 'agency_footer.php'; ?>

