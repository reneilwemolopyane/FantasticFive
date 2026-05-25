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
    <tbody id="bookings-table-body">
      <tr>
        <td colspan="7" style="text-align: center; color: #64748b; padding: 2rem;">
          Retrieving live traveler reservation queues...
        </td>
      </tr>
    </tbody>
  </table>
</div>
<?php include 'agency_footer.php'; ?>