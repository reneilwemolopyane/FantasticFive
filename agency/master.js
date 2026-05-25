document.addEventListener("DOMContentLoaded", () => {
    initLiveDashboardCounters();
    initAnalyticsEngineView();
    initCreatePackageForm();
    initActiveCatalogManagement();
    initClientReservationsQueue();
    initGroupTravelExpeditions();
});

function initLiveDashboardCounters() {
    if (!document.querySelector(".stats-grid")) return;

    const payload = { type: "FetchDashboardSummary" };

    transmitAgencyRequest("api/dashboard_handler.php", payload).then(data => {
        if (data) {
            if (document.getElementById("total-packages-count")) {
                document.getElementById("total-packages-count").innerText = data.total_packages;
            }
            if (document.getElementById("active-bookings-count")) {
                document.getElementById("active-bookings-count").innerText = data.active_bookings;
            }
            if (document.getElementById("revenue-sum")) {
                document.getElementById("revenue-sum").innerText = "R" + parseFloat(data.revenue_collected).toLocaleString();
            }
            if (document.getElementById("active-groups-count")) {
                document.getElementById("active-groups-count").innerText = data.group_trips;
            }
        }
    });
}

function initAnalyticsEngineView() {
    if (!document.querySelector(".analytics-grid-2x") || !document.querySelector("main h1").innerText.includes("Database")) return;

    const payload = { type: "FetchDetailedAnalytics" };

    transmitAgencyRequest("api/analytics_handler.php", payload).then(data => {
        if (data) {
            console.log("Analytics pulled down via JSON channel:", data);
        }
    });
}

function initCreatePackageForm() {
    const form = document.getElementById("packageForm") || document.querySelector(".package-form");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const payload = {
            type: "CreatePackage",
            title: document.getElementById("package_name").value.trim(),
            destination: document.getElementById("destination").value.trim(),
            price: parseFloat(document.getElementById("price").value || 0),
            duration: parseInt(document.getElementById("duration").value || 0),
            description: document.getElementById("description").value.trim(),
            accommodation: document.getElementById("accommodation").value.trim(),
            flights: document.getElementById("flights").value.trim(),
            restaurants: document.getElementById("restaurants").value.trim(),
            transport: document.getElementById("transport").value.trim(),
            attractions: document.getElementById("attractions").value.trim()
        };

        if (payload.price <= 0 || payload.duration <= 0) {
            alert("Please provide realistic and positive numeric metrics.");
            return;
        }

        const responseData = await transmitAgencyRequest("api/package_handler.php", payload);
        if (responseData) {
            alert("Travel package successfully processed and pushed live to marketplace!");
            window.location.href = "manage_package.php";
        }
    });
}

function initActiveCatalogManagement() {
    if (!document.querySelector(".management-view-container") || !document.querySelector("h1").innerText.includes("Catalog")) return;

    window.editPackagePrice = async (packageId) => {
        const newPrice = prompt("Enter updated base price (ZAR):");
        if (!newPrice || isNaN(newPrice) || parseFloat(newPrice) <= 0) return;

        const payload = {
            type: "UpdatePackagePrice",
            id: parseInt(packageId),
            price: parseFloat(newPrice)
        };

        const responseData = await transmitAgencyRequest("api/package_handler.php", payload);
        if (responseData) {
            window.location.reload();
        }
    };

    window.togglePackageVisibility = async (packageId) => {
        const payload = {
            type: "TogglePackageVisibility",
            id: parseInt(packageId)
        };

        const responseData = await transmitAgencyRequest("api/package_handler.php", payload);
        if (responseData) {
            const badge = document.getElementById(`status-label-${packageId}`);
            if (badge) {
                badge.innerText = responseData.new_status;
            }
        }
    };

    window.deletePackageEntity = async (packageId) => {
        if (!confirm("Permanently drop this travel package asset from your active database?")) return;

        const payload = {
            type: "DeletePackage",
            id: parseInt(packageId)
        };

        const responseData = await transmitAgencyRequest("api/package_handler.php", payload);
        if (responseData) {
            const targetRow = document.getElementById(`package-row-${packageId}`);
            if (targetRow) {
                targetRow.remove();
            } else {
                window.location.reload();
            }
        }
    };
}

function initClientReservationsQueue() {
    if (!document.querySelector(".management-view-container") || !document.querySelector("h1").innerText.includes("Reservations")) return;

    window.alterReservationState = async (bookingId, actionWord) => {
        let dbStatus = "";
        if (actionWord === "Approve") dbStatus = "APPROVED";
        if (actionWord === "Reject") dbStatus = "REJECTED";
        if (actionWord === "Cancel") dbStatus = "CANCELLED";

        if (!confirm(`Are you sure you want to change reservation #${bookingId} to ${dbStatus}?`)) return;

        const payload = {
            type: "UpdateBookingStatus",
            id: bookingId,
            status: dbStatus
        };

        const responseData = await transmitAgencyRequest("api/booking_handler.php", payload);
        if (responseData) {
            alert(`Booking status updated to ${dbStatus}.`);
            window.location.reload();
        }
    };
}

function initGroupTravelExpeditions() {
    if (!document.querySelector(".group-trips-view-space")) return;

    window.registerGroupParticipant = async (tripId) => {
        const payload = {
            type: "IncrementGroupTrip",
            id: parseInt(tripId)
        };

        const responseData = await transmitAgencyRequest("api/group_handler.php", payload);
        if (responseData) {
            window.location.reload();
        }
    };
}

async function transmitAgencyRequest(endpointUrl, payloadObject) {
    try {
        const agencyToken = sessionStorage.getItem("api_key");
        
        if (agencyToken && !payloadObject.api_key) {
            payloadObject.api_key = agencyToken;
        }

        const response = await fetch(endpointUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payloadObject)
        });

        const jsonResult = await response.json();

        if (jsonResult.status === "success") {
            return jsonResult.data;
        } else {
            alert(`Agency Portal Error: ${jsonResult.data}`);
            return null;
        }
    } catch (networkError) {
        console.error("Critical network interface drop during API transaction:", networkError);
        alert("Unable to communicate securely with Tripistry Core systems. Check your server connection.");
        return null;
    }
}