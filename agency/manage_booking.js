
function alterReservationState(bookingId, directiveCommand) {
    const statusTargetBadge = document.getElementById(`booking-status-${bookingId}`);
    if (!statusTargetBadge) return;

    console.log(`Executing transaction update directive rule trace: [${directiveCommand}] target processing reference sequence ID: #${bookingId}`);

    switch (directiveCommand) {
        case 'Approve':
            statusTargetBadge.innerText = "APPROVED";
            statusTargetBadge.style.background = "#dcfce7";
            statusTargetBadge.style.color = "#16a34a";
            break;
        case 'Reject':
            statusTargetBadge.innerText = "REJECTED";
            statusTargetBadge.style.background = "#fef2f2";
            statusTargetBadge.style.color = "#dc2626";
            break;
        case 'Cancel':
            statusTargetBadge.innerText = "CANCELLED";
            statusTargetBadge.style.background = "#f1f5f9";
            statusTargetBadge.style.color = "#475569";
            break;
    }
}