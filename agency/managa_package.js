
function editPackagePrice(packageId) {
    const newPrice = prompt("Input updated transactional price value asset threshold base metric:");
    if (newPrice && !isNaN(newPrice)) {
        alert("Success. Network data payload entry configuration synchronized target price modification payload value reference to: R" + parseFloat(newPrice).toFixed(2));
        
    } else if (newPrice) {
        alert("Invalid notation pattern layout parsed.");
    }
}

function togglePackageVisibility(packageId) {
    const statusLabel = document.getElementById(`status-label-${packageId}`);
    if (statusLabel) {
        if (statusLabel.innerText === "ACTIVE") {
            statusLabel.innerText = "UNAVAILABLE";
            statusLabel.style.background = "#fee2e2";
            statusLabel.style.color = "#dc2626";
        } else {
            statusLabel.innerText = "ACTIVE";
            statusLabel.style.background = "#dcfce7";
            statusLabel.style.color = "#16a34a";
        }
    }
}

function deletePackageEntity(packageId) {
    if (confirm("Are you certain you wish to completely drop this custom travel package configuration map profile?")) {
        const row = document.getElementById(`package-row-${packageId}`);
        if(row) row.remove();
    }
}