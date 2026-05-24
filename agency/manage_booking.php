<?php include 'agency_header.php'; ?>
<link rel="stylesheet" href="booking.css">

<div class="management-view-container">
  <div class="section-header-flex">
    <div>
      <h1>Client Reservations Queue</h1>
      <p class="subtitle">Review entries requests, handle payments pipelines, and authorize seat fulfillments.</p>
    </div>
  </div>

  <div class="table-responsive-wrapper">
    <table class="dashboard-data-table">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Traveler Profile</th>
          <th>Package Reserved</th>
          <th>Departure Date</th>
          <th>Transactional Total</th>
          <th>Fulfillment Status</th>
          <th class="text-center">Reservation Controls</th>
        </tr>
      </thead>
      <tbody>
        <tr id="booking-row-7842">
          <td class="monospaced-currency">#TRP7842</td>
          <td>
            <strong>Sarah Johnson</strong><br>
            <small class="muted-text">sarah.j@mail.com</small>
          </td>
          <td>Japan Escape Experience</td>
          <td>15 Jun 2026</td>
          <td class="monospaced-currency">R15,999.00</td>
          <td><span class="status-badge live" id="booking-status-7842" style="background:#fef3c7; color:#d97706;">Pending</span></td>
          <td>
            <div class="action-btn-cluster center-content">
              <button class="btn-action approve" onclick="alterReservationState('7842', 'Approve')">Approve</button>
              <button class="btn-action toggle" onclick="alterReservationState('7842', 'Reject')">Reject</button>
              <button class="btn-action delete" onclick="alterReservationState('7842', 'Cancel')">Cancel</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

</div> <?php include 'agency_footer.php'; ?>
</body>
</html>