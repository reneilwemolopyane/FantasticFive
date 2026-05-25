<?php include 'agency_header.php'; ?>
<link rel="stylesheet" href="booking.css">

<div class="management-view-container">
  <div class="section-header-flex">
    <div>
      <h1>Client Reservations Queue</h1>
      <p class="subtitle">Review booking requests, manage payment pipelines, and authorise seat fulfillments.</p>
    </div>
    <!-- Live counts -->
    <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
      <span class="status-badge" style="background:#fef3c7; color:#d97706; font-size:.85rem; padding:.35rem .9rem;">
        Pending: <strong id="count-pending">—</strong>
      </span>
      <span class="status-badge" style="background:#dcfce7; color:#16a34a; font-size:.85rem; padding:.35rem .9rem;">
        Approved: <strong id="count-approved">—</strong>
      </span>
      <span class="status-badge" style="background:#fee2e2; color:#dc2626; font-size:.85rem; padding:.35rem .9rem;">
        Rejected/Cancelled: <strong id="count-closed">—</strong>
      </span>
    </div>
  </div>

  <!-- Filter bar -->
  <div class="filter-bar" style="margin:1.2rem 0; display:flex; gap:.75rem; flex-wrap:wrap; align-items:center;">
    <input type="text" id="bk-search" placeholder="Search traveller name, email or package…"
           style="padding:.5rem .85rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; flex:1; min-width:220px;">
    <select id="bk-filter-status" style="padding:.5rem .85rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem;">
      <option value="">All Statuses</option>
      <option value="Pending">Pending</option>
      <option value="APPROVED">Approved</option>
      <option value="REJECTED">Rejected</option>
      <option value="CANCELLED">Cancelled</option>
    </select>
    <button onclick="exportCSV()" style="padding:.5rem 1rem; border:1px solid #cbd5e1; border-radius:8px; background:#f8fafc; font-size:.9rem; cursor:pointer;">⬇ Export CSV</button>
  </div>

  <div class="table-responsive-wrapper">
    <table class="dashboard-data-table">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Traveller Profile</th>
          <th>Package Reserved</th>
          <th>Booking Date</th>
          <th>Seats</th>
          <th>Transactional Total</th>
          <th>Fulfilment Status</th>
          <th class="text-center">Reservation Controls</th>
        </tr>
      </thead>
      <tbody id="bookings-table-body">
        <tr>
          <td colspan="8" style="text-align:center; color:#64748b; padding:2rem;">
            Retrieving live traveller reservation queue…
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <p id="bk-empty-msg" style="display:none; text-align:center; color:#64748b; margin-top:1rem;">
    No bookings match your current filters.
  </p>
</div>

<!-- ── Confirm Action Modal ──────────────────────────────────────────────── -->
<div id="action-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1000; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:14px; padding:2rem; width:400px; box-shadow:0 8px 32px rgba(0,0,0,.18);">
    <h3 id="modal-action-title" style="margin:0 0 .5rem;"></h3>
    <p id="modal-action-body" style="color:#64748b; font-size:.9rem; margin:0 0 1.4rem;"></p>
    <div style="display:flex; gap:.75rem; justify-content:flex-end;">
      <button onclick="closeActionModal()" style="padding:.55rem 1.2rem; border:1px solid #cbd5e1; border-radius:8px; background:#f8fafc; cursor:pointer;">Cancel</button>
      <button id="modal-confirm-btn" style="padding:.55rem 1.4rem; border:none; border-radius:8px; font-weight:600; cursor:pointer;">Confirm</button>
    </div>
  </div>
</div>

<script>
// ── State ────────────────────────────────────────────────────────────────────
let allBookings = [];
let pendingAction = null; // { id, status }

// ── Bootstrap ────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  loadBookings();
  document.getElementById("bk-search").addEventListener("input", renderTable);
  document.getElementById("bk-filter-status").addEventListener("change", renderTable);
});

// ── Fetch ─────────────────────────────────────────────────────────────────────
async function loadBookings() {
  const data = await transmitAgencyRequest("api.php", { type: "GetAllBookings" });
  if (!data) return;
  allBookings = data;
  renderTable();
  updateCounts();
}

// ── Counts ────────────────────────────────────────────────────────────────────
function updateCounts() {
  document.getElementById("count-pending").textContent =
    allBookings.filter(b => b.status?.toUpperCase() === "PENDING").length;
  document.getElementById("count-approved").textContent =
    allBookings.filter(b => b.status?.toUpperCase() === "APPROVED").length;
  document.getElementById("count-closed").textContent =
    allBookings.filter(b => ["REJECTED","CANCELLED"].includes(b.status?.toUpperCase())).length;
}

// ── Filter + render ───────────────────────────────────────────────────────────
function renderTable() {
  const search  = document.getElementById("bk-search").value.toLowerCase();
  const statusF = document.getElementById("bk-filter-status").value.toUpperCase();

  const filtered = allBookings.filter(b => {
    const matchText = [b.customer_name, b.customer_email, b.package_title]
      .some(s => (s || "").toLowerCase().includes(search));
    const matchStatus = !statusF || b.status?.toUpperCase() === statusF;
    return matchText && matchStatus;
  });

  const tbody    = document.getElementById("bookings-table-body");
  const emptyMsg = document.getElementById("bk-empty-msg");

  if (filtered.length === 0) {
    tbody.innerHTML = "";
    emptyMsg.style.display = "block";
    return;
  }
  emptyMsg.style.display = "none";

  tbody.innerHTML = filtered.map(b => {
    const st        = (b.status || "Pending").toUpperCase();
    const badgeStyle = statusStyle(st);
    const isClosed  = ["APPROVED","REJECTED","CANCELLED"].includes(st);

    return `
      <tr id="booking-row-${b.id}">
        <td class="monospaced-currency">#TRP${b.id}</td>
        <td>
          <strong>${escHtml(b.customer_name)}</strong><br>
          <small class="muted-text">${escHtml(b.customer_email)}</small>
        </td>
        <td>${escHtml(b.package_title)}</td>
        <td>${escHtml(b.booking_date)}</td>
        <td style="text-align:center;">${b.seats || 1}</td>
        <td class="monospaced-currency">R${fmtMoney(b.price)}</td>
        <td><span id="booking-status-${b.id}" class="status-badge" style="${badgeStyle}">${escHtml(b.status)}</span></td>
        <td>
          <div class="action-btn-cluster center-content">
            ${!isClosed || st === 'PENDING' ? `
              <button class="btn-action approve" onclick="confirmAction(${b.id},'APPROVED')"  ${st==='APPROVED'?'disabled':''}>Approve</button>
              <button class="btn-action toggle"  onclick="confirmAction(${b.id},'REJECTED')"  ${st==='REJECTED'?'disabled':''}>Reject</button>
            ` : ''}
            <button class="btn-action delete" onclick="confirmAction(${b.id},'CANCELLED')" ${st==='CANCELLED'?'disabled':''}>Cancel</button>
          </div>
        </td>
      </tr>
    `;
  }).join("");
}

// ── Status badge colours ──────────────────────────────────────────────────────
function statusStyle(st) {
  if (st === "APPROVED")  return "background:#dcfce7; color:#16a34a;";
  if (st === "REJECTED")  return "background:#fee2e2; color:#dc2626;";
  if (st === "CANCELLED") return "background:#f1f5f9; color:#64748b;";
  return "background:#fef3c7; color:#d97706;"; // PENDING
}

// ── Action modal ──────────────────────────────────────────────────────────────
const actionLabels = {
  APPROVED:  { title: "Approve Booking",  colour: "#16a34a" },
  REJECTED:  { title: "Reject Booking",   colour: "#dc2626" },
  CANCELLED: { title: "Cancel Booking",   colour: "#64748b" }
};

function confirmAction(bookingId, status) {
  const bk  = allBookings.find(b => b.id === bookingId);
  const cfg = actionLabels[status] || { title: status, colour: "#3b82f6" };

  pendingAction = { id: bookingId, status };

  document.getElementById("modal-action-title").textContent = cfg.title;
  document.getElementById("modal-action-body").innerHTML =
    `Booking <strong>#TRP${bookingId}</strong> for <strong>${escHtml(bk?.customer_name || '')}</strong><br>
     Package: <em>${escHtml(bk?.package_title || '')}</em><br><br>
     This will mark the reservation as <strong>${status}</strong>.`;

  const btn = document.getElementById("modal-confirm-btn");
  btn.textContent = cfg.title;
  btn.style.background = cfg.colour;
  btn.style.color = "#fff";

  document.getElementById("action-modal").style.display = "flex";
}

function closeActionModal() {
  document.getElementById("action-modal").style.display = "none";
  pendingAction = null;
}

document.getElementById("modal-confirm-btn").addEventListener("click", async () => {
  if (!pendingAction) return;
  const { id, status } = pendingAction;
  closeActionModal();
  await alterReservationState(id, status);
});

async function alterReservationState(bookingId, status) {
  const res = await transmitAgencyRequest("api.php", {
    type: "UpdateBookingStatus",
    id: bookingId,
    status
  });

  if (res) {
    const bk = allBookings.find(b => b.id === bookingId);
    if (bk) bk.status = status;
    renderTable();
    updateCounts();
  }
}

// ── CSV Export ────────────────────────────────────────────────────────────────
function exportCSV() {
  const headers = ["Booking ID","Traveller","Email","Package","Date","Seats","Total (ZAR)","Status"];
  const rows = allBookings.map(b => [
    `#TRP${b.id}`,
    b.customer_name,
    b.customer_email,
    b.package_title,
    b.booking_date,
    b.seats || 1,
    parseFloat(b.price || 0).toFixed(2),
    b.status
  ]);

  const csv = [headers, ...rows].map(r =>
    r.map(c => `"${String(c).replace(/"/g,'""')}"`).join(",")
  ).join("\n");

  const blob = new Blob([csv], { type: "text/csv" });
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement("a");
  a.href     = url;
  a.download = `bookings_${new Date().toISOString().slice(0,10)}.csv`;
  a.click();
  URL.revokeObjectURL(url);
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmtMoney(v) {
  return parseFloat(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escHtml(str) {
  return String(str || '').replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
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