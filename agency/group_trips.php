<script>

document.addEventListener("DOMContentLoaded", loadGroupTrips);

async function loadGroupTrips() {

    const container = document.getElementById("group-trips-container");

    const trips = await transmitAgencyRequest("api.php", {
        type: "GetAllGroupTrips"
    });

    if (!trips || trips.length === 0) {

        container.innerHTML = `
            <p style="padding:20px;">
                No active group trips found.
            </p>
        `;

        return;
    }

    container.innerHTML = "";

    trips.forEach(trip => {

        const card = `
            <div class="trip-profile-card">

                <div class="card-hero-banner">
                    <img src="${trip.image}" alt="${trip.title}">

                    <span class="departure-tag">
                        Depart: ${trip.departure_date}
                    </span>
                </div>

                <div class="card-body-content">

                    <h3>${trip.title}</h3>

                    <p class="duration-sub">
                        ${trip.duration} Day Guided Excursion
                    </p>

                    <div class="occupancy-metrics-box">

                        <div class="metrics-labels">

                            <span>
                                Seats Filled:
                                <strong>${trip.seats_filled}</strong>
                            </span>

                            <span>
                                Max Limit:
                                <strong>${trip.max_seats}</strong>
                            </span>

                        </div>

                        <div class="progress-track-rail">

                            <div class="progress-fill-indicator"
                                 style="width:${trip.percentage}%;">
                            </div>

                        </div>

                        <p class="percentage-caption">
                            ${trip.percentage}% Capacity Filled
                        </p>

                    </div>

                    <div class="group-trips-actions">

                        <button class="btn-manage-group">
                            View Participants
                        </button>

                    </div>

                </div>

            </div>
        `;

        container.insertAdjacentHTML("beforeend", card);
    });
}

</script>

<?php
$page_css = 'group_trips.css';
include 'agency_header.php';
?>

<div class="group-trips-view-space">
  <div class="section-header-flex">
    <div>
      <h1>Group Travel Expeditions</h1>
      <p class="subtitle">Monitor seat thresholds, track upcoming departures, and manage registration profiles.</p>
    </div>
  </div>

  <div class="cards-grid-layout">
    <div class="cards-grid-layout" id="group-trips-container">
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

<?php include 'agency_footer.php'; ?>