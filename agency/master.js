 document.addEventListener("DOMContentLoaded", () => {
    initLiveDashboardCounters();
    initAnalyticsEngineView();
    initCreatePackageForm();
    initActiveCatalogManagement();
});

function initLiveDashboardCounters() {
    const statsGrid = document.querySelector(".stats-grid");
    if (!statsGrid) return;

    const payload = { 
        type: "FetchDashboardSummary" 
    };

    transmitAgencyRequest("api.php", payload).then(data => {
        if (data) {
            if (document.getElementById("total-packages-count")) {
                document.getElementById("total-packages-count").innerText = data.total_packages;
            }
            if (document.getElementById("active-bookings-count")) {
                document.getElementById("active-bookings-count").innerText = data.active_bookings;
            }
            if (document.getElementById("revenue-sum")) {
                document.getElementById("revenue-sum").innerText = "R" + parseFloat(data.revenue_collected).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

    const payload = { 
        type: "FetchDetailedAnalytics" 
    };

    transmitAgencyRequest("api.php", payload).then(data => {
        if (data && data.popular_packages) {
            metricsContainer.innerHTML = "";
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
    const form = document.getElementById("packageForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const priceInput = document.getElementById("price") ? parseFloat(document.getElementById("price").value) : 0;
        const durationInput = document.getElementById("duration") ? parseInt(document.getElementById("duration").value) : 0;

        if (priceInput <= 0 || durationInput <= 0) {
            alert("Please enter a positive value.");
            return;
        }

        const formData = new FormData(form);
        if (!formData.has("type")) {
            formData.append("type", "CreatePackage");
        }

        const responseData = await transmitAgencyRequest("api.php", formData);
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
            const rowHTML = `
                <tr id="package-row-${pkg.id}">
                    <td><div class="thumb-crop"><img src="../frontend/Japan_package.jpeg" alt="${pkg.title}"></div></td>
                    <td><strong>${pkg.title}</strong></td>
                    <td>${pkg.destination}</td>
                    <td class="monospaced-currency">R${parseFloat(pkg.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    <td>${pkg.duration} Scheduled Days</td>
                    <td><span id="status-label-${pkg.id}" class="status-badge live">${pkg.status || 'Active'}</span></td>
                    <td>
                        <div class="action-btn-cluster">
                            <button class="btn-action edit" onclick="editPackagePrice(${pkg.id})">Update Price</button>
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
            id: packageId,
            price: parseFloat(newPrice)
        };

        const res = await transmitAgencyRequest("api.php", payload);
        if (res) {
            window.location.reload();
        }
    };

    window.deletePackageEntity = async (packageId) => {
        if (!confirm("Are you sure you want to permanently delete this package?")) return;

        const payload = {
            type: "DeletePackage",
            id: packageId
        };

        const res = await transmitAgencyRequest("api.php", payload);
        if (res) {
            const row = document.getElementById(`package-row-${packageId}`);
            if (row) row.remove();
        }
    };
}

async function transmitAgencyRequest(endpointUrl, payloadObject) {
    try {
        let fetchOptions = { 
            method: "POST" 
        };

        if (payloadObject instanceof FormData) {
            fetchOptions.body = payloadObject;
        } else {
            fetchOptions.headers = { 
                "Content-Type": "application/json" 
            };
            fetchOptions.body = JSON.stringify(payloadObject);
        }

        const response = await fetch(endpointUrl, fetchOptions);
        const rawText = await response.text();
          // SHOW EXACT PHP OUTPUT
        console.log("RAW RESPONSE:");
            console.log(rawText);

         const jsonResult = JSON.parse(rawText);

        if (jsonResult.status === "success") {
            return jsonResult.data || true;
        } else {
            alert(`Agency Portal Error: ${jsonResult.data}`);
            return null;
        }
    } catch (networkError) {
        console.error("Critical network interface drop:", networkError);
        return null;
    }
}