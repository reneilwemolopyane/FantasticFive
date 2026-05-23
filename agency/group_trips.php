<?php include 'agency_header.php'; ?>
<link rel="stylesheet" href="group_trips.css">

<div class="group-trips-view-space">
  <div class="section-header-flex">
    <div>
      <h1>Group Travel Expeditions</h1>
      <p class="subtitle">Monitor seat thresholds, track upcoming departures, and manage registration profiles.</p>
    </div>
  </div>

  <div class="cards-grid-layout">
    <div class="trip-profile-card" id="group-card-1">
      <div class="card-hero-banner">
        <img src="../frontend/CPT_attraction.jpeg" alt="Cape Town Expedition">
        <span class="departure-tag">Depart: 10 June 2026</span>
      </div>
      <div class="card-body-content">
        <h3>Cape Town Adventure Group</h3>
        <p class="duration-sub">8-Day Guided Sightseeing Excursion</p>
        
        <div class="occupancy-metrics-box">
          <div class="metrics-labels">
            <span>Seats Filled: <strong id="seats-filled-1">8</strong></span>
            <span>Max Limit: <strong>12</strong></span>
          </div>
          <div class="progress-track-rail">
            <div class="progress-fill-indicator" id="progress-bar-1" style="width: 66.6%;"></div>
          </div>
          <p class="percentage-caption" id="pct-caption-1">66% Capacity Filled</p>
        </div>

        <div class="group-trips-actions" style="display: flex; gap: 8px;">
          <button class="btn-manage-group" onclick="registerGroupParticipant(1)">+ Add Participant</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="group_trips.js" defer></script>
</body>
</html>