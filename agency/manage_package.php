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

  <div class="table-responsive-wrapper">
    <table class="dashboard-data-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Package Identity</th>
          <th>Destination</th>
          <th>Price Structure</th>
          <th>Duration Scale</th>
          <th>Live Status</th>
          <th class="text-center">Operations Options</th>
        </tr>
      </thead>
      <tbody>
        <tr id="package-row-1">
          <td><div class="thumb-crop"><img src="../frontend/Japan_package.jpeg" alt="Japan Showcase"></div></td>
          <td><strong>Japan Escape Experience</strong></td>
          <td>Tokyo, Japan</td>
          <td class="monospaced-currency">R15,999.00</td>
          <td>5 Scheduled Days</td>
          <td><span class="status-badge live" id="status-label-1">Active</span></td>
          <td>
            <div class="action-btn-cluster">
              <button class="btn-action edit" onclick="editPackagePrice(1)">Update Price</button>
              <button class="btn-action toggle" onclick="togglePackageVisibility(1)">Delist Package</button>
              <button class="btn-action delete" onclick="deletePackageEntity(1)">Delete</button>
            </div>
          </td>
        </tr>
        <tr id="package-row-2">
          <td><div class="thumb-crop"><img src="../frontend/Mauritius_package.jpeg" alt="Mauritius Showcase"></div></td>
          <td><strong>Mauritius Premium Solitude</strong></td>
          <td>Port Louis, Mauritius</td>
          <td class="monospaced-currency">R18,500.00</td>
          <td>7 Scheduled Days</td>
          <td><span class="status-badge live" id="status-label-2">Active</span></td>
          <td>
            <div class="action-btn-cluster">
              <button class="btn-action edit" onclick="editPackagePrice(2)">Update Price</button>
              <button class="btn-action toggle" onclick="togglePackageVisibility(2)">Delist Package</button>
              <button class="btn-action delete" onclick="deletePackageEntity(2)">Delete</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<?php include 'agency_footer.php'; ?>