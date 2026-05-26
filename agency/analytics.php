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

    <div class="analytics-grid-2x" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem; margin-top:1.5rem;">
      <div class="chart-container-card" style="background:#fff; border-radius:12px; padding:1.5rem; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <h3>All-Time Top Destinations (Static Metrics)</h3>
        <ul class="analytics-data-list" style="list-style:none; padding:0; margin:1rem 0 0 0; display:flex; flex-direction:column; gap:0.75rem;">
          <li style="display:flex; justify-content:between; border-bottom:1px solid #f1f5f9; padding-bottom:0.5rem;"><span style="flex:1;">Tokyo, Japan</span> <strong>45 Bookings</strong></li>
          <li style="display:flex; justify-content:between; border-bottom:1px solid #f1f5f9; padding-bottom:0.5rem;"><span style="flex:1;">Cape Town, South Africa</span> <strong>38 Bookings</strong></li>
          <li style="display:flex; justify-content:between; border-bottom:1px solid #f1f5f9; padding-bottom:0.5rem;"><span style="flex:1;">Port Louis, Mauritius</span> <strong>21 Bookings</strong></li>
        </ul>
      </div>

      <div class="chart-container-card" style="background:#fff; border-radius:12px; padding:1.5rem; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
        <h3>Live Product Booking Analytics (Real-Time Database Records)</h3>
        <ul class="analytics-data-list" id="analytics-metrics-render" style="list-style:none; padding:0; margin:1rem 0 0 0; display:flex; flex-direction:column; gap:0.75rem;">
          <li style="color: #64748b;">Loading metrics engine records...</li>
        </ul>
      </div>
    </div>
    
    <div style="margin-top:1.5rem;">
        <small style="color:#64748b;">74% general optimization capacity matching projected target timelines.</small>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    loadLiveAnalyticsMetrics();
});

async function loadLiveAnalyticsMetrics() {
    const renderContainer = document.getElementById("analytics-metrics-render");
    
    try {
        // Construct standard agency request transmission payload wrapper contract
        const payload = { type: "FetchDetailedAnalytics" };
        const token = sessionStorage.getItem("api_key");
        if (token) payload.api_key = token;

        const response = await fetch("api.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const json = await response.json();
        
        if (json.status !== "success" || !json.data || !json.data.popular_packages) {
            renderContainer.innerHTML = `<li style="color:#dc2626;">Failed to process live records calculation metrics framework hooks.</li>`;
            return;
        }

        const metricsCollectionArray = json.data.popular_packages;
        if (metricsCollectionArray.length === 0) {
            renderContainer.innerHTML = `<li style="color:#64748b;">No tracking analytics accumulated yet for this session.</li>`;
            return;
        }

        // Render rows directly into container components loop
        renderContainer.innerHTML = metricsCollectionArray.map(item => {
            return `
                <li style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f1f5f9; padding-bottom:0.5rem;">
                    <div style="flex:1;">
                        <strong style="color:#1e293b; display:block;">${escHtml(item.title)}</strong>
                        <span style="font-size:0.8rem; color:#64748b;">${escHtml(item.destination)}</span>
                    </div>
                    <span style="background:#e0f2fe; color:#0369a1; padding:0.25rem 0.6rem; border-radius:6px; font-size:0.85rem; font-weight:600;">
                        ${parseInt(item.booking_count)} Bookings
                    </span>
                </li>
            `;
        }).join("");

    } catch (error) {
        console.error("Analytics rendering process drop exception caught:", error);
        renderContainer.innerHTML = `<li style="color:#dc2626;">Unable to sync records with analytics engine.</li>`;
    }
}

function escHtml(str) {
    return String(str || '').replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
}
</script>

<?php include 'agency_footer.php'; ?>