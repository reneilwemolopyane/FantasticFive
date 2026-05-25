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

    // UPDATED: Now targets api.php
    transmitAgencyRequest("api.php", payload).then(data => {
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
    const metricsContainer = document.getElementById("analytics-metrics-render");
    if (!metricsContainer) return;

    const payload = { type: "FetchDetailedAnalytics" };

    // UPDATED: Now targets api.php
    transmitAgencyRequest("api.php", payload).then(data => {
        if (data && data.popular_packages) {
            metricsContainer.innerHTML = ""; 
            
            data.popular_packages.forEach(metric => {
                const row = `
                    <div class="analytics-row">
                        <span><strong>${metric.title}</strong> (${metric.destination})</span>
                        <span>${metric.booking_count} Bookings Generated</span>
                    </div>
                `;
                metricsContainer.insertAdjacentHTML("beforeend", row);
            });
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
            title: document.getElementById("Title").value.trim(),
            destination: document.getElementById("destination").value.trim(),
            price: parseFloat(document.getElementById("price").value || 0),
            duration: parseInt(document.getElementById("duration").value || 0),
            description: document.getElementById("description").value.trim(),
            accommodation: document.getElementById("accommodation").value.trim(),
            flights: document.getElementById("flights").value.trim(),
            restaurants: document.getElementById("restaurants").value.trim(),
            transport: document.getElementById("transport").value.trim(),
            attractions: document.getElementById("attractions").value.trim(),
            startDate: document.getElementById("start_date").value.trim(),
            endDate: document.getElementById("end_date").value.trim(),
            maxPeople: document.getElementById("max_people").value.trim(),
            pack_type: document.getElementById("pack_type").value.trim()
        };

        if (payload.price <= 0 || payload.duration <= 0) {
            alert("Please provide realistic and positive numeric metrics.");
            return;
        }

        // UPDATED: Now targets api.php
        const responseData = await transmitAgencyRequest("api.php", payload);
        if (responseData) {
            alert("Travel package successfully processed and pushed live to marketplace!");
            window.location.href = "manage_package.php";
        }
    });
}

function initActiveCatalogManagement() {
    const tableBody = document.getElementById("packages-table-body");
    if (!tableBody) return;

    // UPDATED: Now targets api.php
    transmitAgencyRequest("api.php", { type: "GetAllPackages" }).then(packages => {
        if (!packages) return;
        tableBody.innerHTML = ""; 
        
        packages.forEach(pkg => {
            const rowHTML = `
                <tr id="package-row-${pkg.id}">
                    <td><strong>${pkg.title}</strong><br><small style="color:#64748b">${pkg.destination}</small></td>
                    <td>R ${parseFloat(pkg.price).toLocaleString()}</td>
                    <td>${pkg.duration} Days</td>
                    <td><span id="status-label-${pkg.id}" class="badge">${pkg.status || 'Active'}</span></td>
                    <td>
                        <button class="btn-action edit" onclick="editPackagePrice(${pkg.id})">Price</button>
                        <button class="btn-action toggle" onclick="togglePackageVisibility(${pkg.id})">Toggle</button>
                        <button class="btn-action delete" onclick="deletePackageEntity(${pkg.id})">Drop</button>
                    </td>
                </tr>
            `;
            tableBody.insertAdjacentHTML("beforeend", rowHTML);
        });
    });

    window.editPackagePrice = async (packageId) => {
        const newPrice = prompt("Enter updated base price (ZAR):");
        if (!newPrice || isNaN(newPrice) || parseFloat(newPrice) <= 0) return;

        const payload = {
            type: "UpdatePackagePrice",
            id: parseInt(packageId),
            price: parseFloat(newPrice)
        };

        // UPDATED: Now targets api.php
        const responseData = await transmitAgencyRequest("api.php", payload);
        if (responseData) {
            window.location.reload();
        }
    };

    window.togglePackageVisibility = async (packageId) => {
        const payload = {
            type: "TogglePackageVisibility",
            id: parseInt(packageId)
        };

        // UPDATED: Now targets api.php
        const responseData = await transmitAgencyRequest("api.php", payload);
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

        // UPDATED: Now targets api.php
        const responseData = await transmitAgencyRequest("api.php", payload);
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
    const bookingsTableBody = document.getElementById("bookings-table-body");
    if (!bookingsTableBody) return;

    // UPDATED: Now targets api.php
    transmitAgencyRequest("api.php", { type: "GetAllBookings" }).then(bookings => {
        if (!bookings) return;
        bookingsTableBody.innerHTML = ""; 

        bookings.forEach(booking => {
            const rowHTML = `
                <tr id="booking-row-${booking.id}">
                    <td>#${booking.id}</td>
                    <td><strong>${booking.customer_name}</strong><br><small>${booking.customer_email}</small></td>
                    <td>${booking.package_title}</td>
                    <td>${booking.booking_date}</td>
                    <td><span class="badge-status-${booking.status.toLowerCase()}">${booking.status}</span></td>
                    <td>
                        <button class="btn-action approve" onclick="alterReservationState(${booking.id}, 'Approve')">Approve</button>
                        <button class="btn-action reject" onclick="alterReservationState(${booking.id}, 'Reject')">Reject</button>
                    </td>
                </tr>
            `;
            bookingsTableBody.insertAdjacentHTML("beforeend", rowHTML);
        });
    });

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

        // UPDATED: Now targets api.php
        const responseData = await transmitAgencyRequest("api.php", payload);
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

        // UPDATED: Now targets api.php
        const responseData = await transmitAgencyRequest("api.php", payload);
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
        alert("Unable to communicate securely with Core systems.");
        return null;
    }
}