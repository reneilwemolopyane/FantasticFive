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

    transmitAgencyRequest("api.php", payload).then(data => {
        if (data) {
            if (document.getElementById("total-packages-count")) {
                document.getElementById("total-packages-count").innerText = data.total_packages;
            }
            if (document.getElementById("active-bookings-count")) {
                document.getElementById("active-bookings-count").innerText = data.active_bookings;
            }
            if (document.getElementById("revenue-sum")) {
                document.getElementById("revenue-sum").innerText = "R" + parseFloat(data.revenue_collected).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
            if (document.getElementById("active-groups-count")) {
                document.getElementById("active-groups-count").innerText = data.group_trips;
            }
        }
    });
}

function initAnalyticsEngineView() {
    // Check both potential list targets across dashboard extensions and analytics page views
    const metricsContainer = document.getElementById("analytics-metrics-render") || document.querySelector(".analytics-data-list");
    if (!metricsContainer) return;

    const payload = { type: "FetchDetailedAnalytics" };

    transmitAgencyRequest("api.php", payload).then(data => {
        if (data && data.popular_packages) {
            metricsContainer.innerHTML = ""; // Clear existing hardcoded rows
            
            data.popular_packages.forEach(metric => {
                const itemHTML = `
                    <li>
                        <span><strong>${metric.title}</strong> (${metric.destination})</span>
                        <strong>${metric.booking_count} Bookings</strong>
                    </li>
                `;
                metricsContainer.insertAdjacentHTML("beforeend", itemHTML);
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
            accommodation: document.getElementById("accommodation").value?.trim() || "",
            flights: document.getElementById("flights").value?.trim() || "",
            restaurants: document.getElementById("restaurants").value?.trim() || "",
            transport: document.getElementById("transport").value?.trim() || "",
            attractions: document.getElementById("attractions").value?.trim() || "",
            startDate: document.getElementById("start_date")?.value || "",
            endDate: document.getElementById("end_date")?.value || "",
            maxPeople: document.getElementById("max_people")?.value || "10",
            pack_type: document.getElementById("pack_type")?.value || "Leisure"
        };

        if (payload.price <= 0 || payload.duration <= 0) {
            alert("Please provide realistic and positive numeric metrics.");
            return;
        }

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

    transmitAgencyRequest("api.php", { type: "GetAllPackages" }).then(packages => {
        if (!packages) return;
        tableBody.innerHTML = ""; 
        
        if (packages.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#64748b; padding:2rem;">No active travel packages found.</td></tr>`;
            return;
        }

        packages.forEach(pkg => {
           
            const imageSrc = pkg.image_url ? pkg.image_url : "../frontend/Japan_package.jpeg";
            const rowHTML = `
                <tr id="package-row-${pkg.id}">
                    <td><div class="thumb-crop"><img src="${imageSrc}" alt="${pkg.title}"></div></td>
                    <td><strong>${pkg.title}</strong></td>
                    <td>${pkg.destination}</td>
                    <td class="monospaced-currency">R${parseFloat(pkg.price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td>${pkg.duration} Scheduled Days</td>
                    <td><span id="status-label-${pkg.id}" class="status-badge live">${pkg.status || 'Active'}</span></td>
                    <td>
                        <div class="action-btn-cluster">
                            <button class="btn-action edit" onclick="editPackagePrice(${pkg.id})">Update Price</button>
                            <button class="btn-action toggle" onclick="togglePackageVisibility(${pkg.id})">Delist Package</button>
                            <button class="btn-action delete" onclick="deletePackageEntity(${pkg.id})">Delete</button>
                        </div>
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

        const responseData = await transmitAgencyRequest("api.php", payload);
        if (responseData) {
            const badge = document.getElementById(`status-label-${packageId}`);
            if (badge) {
                badge.innerText = responseData.new_status;
                if(responseData.new_status === "Delisted" || responseData.new_status === "Hidden") {
                    badge.style.background = "#ef4444";
                    badge.style.color = "#ffffff";
                } else {
                    badge.style.background = ""; 
                    badge.style.color = "";
                }
            }
        }
    };

    window.deletePackageEntity = async (packageId) => {
        if (!confirm("Permanently drop this travel package asset from your active database?")) return;

        const payload = {
            type: "DeletePackage",
            id: parseInt(packageId)
        };

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

    transmitAgencyRequest("api.php", { type: "GetAllBookings" }).then(bookings => {
        if (!bookings) return;
        bookingsTableBody.innerHTML = ""; 

        if (bookings.length === 0) {
            bookingsTableBody.innerHTML = `<tr><td colspan="7" style="text-align:center; color:#64748b; padding:2rem;">No client reservations pending processing.</td></tr>`;
            return;
        }

        bookings.forEach(booking => {
        
            const statusStyle = booking.status.toUpperCase() === "PENDING" ? "background:#fef3c7; color:#d97706;" : "";
            const rowHTML = `
                <tr id="booking-row-${booking.id}">
                    <td class="monospaced-currency">#TRP${booking.id}</td>
                    <td>
                        <strong>${booking.customer_name}</strong><br>
                        <small class="muted-text">${booking.customer_email}</small>
                    </td>
                    <td>${booking.package_title}</td>
                    <td>${booking.booking_date}</td>
                    <td class="monospaced-currency">R${parseFloat(booking.price || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                    <td><span class="status-badge live" id="booking-status-${booking.id}" style="${statusStyle}">${booking.status}</span></td>
                    <td>
                        <div class="action-btn-cluster center-content">
                            <button class="btn-action approve" onclick="alterReservationState(${booking.id}, 'Approve')">Approve</button>
                            <button class="btn-action toggle" onclick="alterReservationState(${booking.id}, 'Reject')">Reject</button>
                            <button class="btn-action delete" onclick="alterReservationState(${booking.id}, 'Cancel')">Cancel</button>
                        </div>
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