

<?php
$page_css = 'form_style.css';
include 'agency_header.php';
?>
<div class="form-page-container">
  <div class="form-header">
    <h1>Create Travel Package</h1>
    <p class="subtitle">Broadcast a new comprehensive trip offering out to the traveler marketplace.</p>
  </div>

  <form id="packageForm" action="api.php" method="POST" enctype="multipart/form-data" class="package-form">
    <input type="hidden" name="type" value="CreatePackage">
    
    <fieldset>
      <legend>Core Package Details</legend>
      
      <div class="form-group">
        <label for="Title">Title</label>
        <input type="text" id="Title" name="Title" placeholder="e.g., Ultimate Luxury Cape Town Escape" required>
      </div>

      <div class="form-grid-2x">
        <div class="form-group">
          <label for="destination">Destination</label>
          <input type="text" id="destination" name="destination" placeholder="e.g., Cape Town, South Africa" required>
        </div>
        
        <div class="form-grid-nested">
          <div class="form-group">
            <label for="price">Price (ZAR R)</label>
            <input type="number" id="price" name="price" min="1" step="0.01" placeholder="15000.00" required>
          </div>
          <div class="form-group">
            <label for="duration">Duration (Days)</label>
            <input type="number" id="duration" name="duration" min="1" placeholder="7" required>
          </div>
        </div>
      </div>

      <div class="form-grid-2x">
        <div class="form-group">
          <label for="start_date">Start Date</label>
          <input type="date" id="start_date" name="start_date" required>
        </div>
        <div class="form-group">
          <label for="end_date">End Date</label>
          <input type="date" id="end_date" name="end_date" required>
        </div>
      </div>

      <div class="form-grid-2x">
        <div class="form-group">
          <label for="max_people">Maximum People</label>
          <input type="number" id="max_people" name="max_people" min="1" required>
        </div>
        <div class="form-group">
          <label for="pack_type">Package Type</label>
          <select id="pack_type" name="pack_type" required>
            <option value="">Select Type</option>
            <option value="Luxury">Luxury</option>
            <option value="Adventure">Adventure</option>
            <option value="Family">Family</option>
            <option value="Romantic">Romantic</option>
            <option value="Budget">Budget</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="5" placeholder="Provide a detailed itinerary overview breakdown..." required></textarea>
      </div>
    </fieldset>

    <fieldset>
      <legend>Group Dynamic Parameters</legend>
      <div class="form-group check-group-row">
        <label for="is_group_trip">
          <input type="checkbox" id="is_group_trip" name="is_group_trip">
          Activate and register this package as an active Group Trip pool
        </label>
      </div>

      <div id="groupTripFields">
        <div class="form-grid-2x">
          <div class="form-group">
            <label>Departure Date</label>
            <input type="date" name="departure_date">
          </div>
          <div class="form-group">
            <label>Maximum Seats</label>
            <input type="number" name="max_seats" min="1">
          </div>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend>Logistics & Hospitality Requirements</legend>
      <div class="form-grid-2x">
        <div class="form-group">
          <label for="accommodation">Accommodation</label>
          <input type="text" id="accommodation" name="accommodation" placeholder="e.g., 5-Star Radisson Blu" required>
        </div>
        <div class="form-group">
          <label for="flights">Flights</label>
          <input type="text" id="flights" name="flights" placeholder="e.g., Return Economy Class Flights" required>
        </div>
      </div>

      <div class="form-grid-2x">
        <div class="form-group">
          <label for="restaurants">Restaurants</label>
          <input type="text" id="restaurants" name="restaurants" placeholder="e.g., Daily Buffet Breakfast Included" required>
        </div>
        <div class="form-group">
          <label for="transport">Transport</label>
          <input type="text" id="transport" name="transport" placeholder="e.g., Private AC Shuttle Coach" required>
        </div>
      </div>

      <div class="form-group">
        <label for="attractions">Attractions</label>
        <input type="text" id="attractions" name="attractions" placeholder="e.g., Table Mountain Cableway, Robben Island" required>
      </div>
    </fieldset>

    <fieldset>
      <legend>Visual Media Assets</legend>
      <div class="form-group">
        <label for="images">Images (Select Multiple)</label>
        <input type="file" id="images" name="images[]" multiple accept="image/*" class="file-custom-input">
        <span class="input-tip">Tip: Hold down Ctrl or Command to pick multiple display photos.</span>
      </div>
    </fieldset>

    <div class="form-actions-wrapper">
      <button type="submit" class="btn-primary-action">Publish Travel Package</button>
    </div>
  </form>
</div>

<?php include 'agency_footer.php'; ?>