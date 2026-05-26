<?php
$page_css = 'manage_package.css';
include 'agency_header.php';
?>

<div class="section-header-flex">
  <div>
    <h1>Active Catalog Management</h1>
    <p class="subtitle">Modify live parameters or temporarily toggle item visibility settings.</p>
  </div>
  <a href="create_package.php" class="btn-new-package">+ Build New Package</a>
</div>

<div class="table-responsive-wrapper">
  <table class="dashboard-data-table">
    <thead>
      <tr>
        <th>Image</th>
        <th>Package Title</th>
        <th>Destination</th>
        <th>Price</th>
        <th>Duration</th>
        <th>Status</th>
        <th>Operations</th>
      </tr>
    </thead>
    <tbody id="packages-table-body">
      <!-- MOCK ROW 1 — always stays, not from DB -->
      <tr id="package-row-mock-1">
        <td><div class="thumb-crop"><img src="../frontend/Japan_package.jpeg" alt="Japan"></div></td>
        <td><strong>Japan Escape Experience</strong></td>
        <td>Tokyo, Japan</td>
        <td class="monospaced-currency">R15,999.00</td>
        <td>5 Scheduled Days</td>
        <td><span class="status-badge live">Active</span></td>
        <td>
          <div class="action-btn-cluster">
            <button class="btn-action edit" disabled title="Mock data">Update Price</button>
            <button class="btn-action toggle" disabled title="Mock data">Delist</button>
            <button class="btn-action delete" disabled title="Mock data">Delete</button>
          </div>
        </td>
      </tr>
      <!-- MOCK ROW 2 — always stays, not from DB -->
      <tr id="package-row-mock-2">
        <td><div class="thumb-crop"><img src="../frontend/Mauritius_package.jpeg" alt="Mauritius"></div></td>
        <td><strong>Mauritius Premium Solitude</strong></td>
        <td>Port Louis, Mauritius</td>
        <td class="monospaced-currency">R18,500.00</td>
        <td>7 Scheduled Days</td>
        <td><span class="status-badge live">Active</span></td>
        <td>
          <div class="action-btn-cluster">
            <button class="btn-action edit" disabled title="Mock data">Update Price</button>
            <button class="btn-action toggle" disabled title="Mock data">Delist</button>
            <button class="btn-action delete" disabled title="Mock data">Delete</button>
          </div>
        </td>
      </tr>
      <!-- DB rows get APPENDED below by master.js -->
    </tbody>
  </table>
</div>

<?php include 'agency_footer.php'; ?>