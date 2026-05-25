<?php
$page_css = 'analytics.css';
include 'agency_header.php';
?>
<div class="analytics-page-container">  
    <div class="section-header-flex">
      <div>
        <h1>Database Analytics Engine</h1>
        <p class="subtitle">Live database statistics driven metrics measuring performance variations.</p>
      </div>
    </div>

    <div class="analytics-grid-2x">
      <div class="chart-container-card">
        <h3>Most Booked Destinations</h3>
        <ul class="analytics-data-list">
          <li><span>Tokyo, Japan</span> <strong>45 Bookings</strong></li>
          <li><span>Cape Town, South Africa</span> <strong>38 Bookings</strong></li>
          <li><span>Port Louis, Mauritius</span> <strong>21 Bookings</strong></li>
        </ul>
      </div>

 <div class="chart-container-card">
  <h3>Most Booked Destinations</h3>
  <ul class="analytics-data-list" id="analytics-metrics-render">
    <li style="color: #64748b;">Loading metrics engine records...</li>
  </ul>
</div>
        <small>74% general optimization capacity matching projected target timelines.</small>
      </div>
    </div>

</div>
<?php include 'agency_footer.php'; ?>