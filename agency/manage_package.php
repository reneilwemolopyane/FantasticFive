
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
    <tbody id="packages-table-body">
      <tr>
        <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">
          Loading active catalog items directly from the database...
        </td>
      </tr>
    </tbody>
  </table>
</div>
<?php include 'agency_footer.php'; ?>