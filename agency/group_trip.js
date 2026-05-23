function addParticipant(id, maxCapacity) {
    const label = document.getElementById(`seats-filled-${id}`);
    const bar = document.getElementById(`progress-bar-${id}`);
    const caption = document.getElementById(`pct-caption-${id}`);
    
    if (label && bar) {
        let count = parseInt(label.innerText);
        if (count < maxCapacity) {
            count++;
            label.innerText = count;
            let pct = Math.round((count / maxCapacity) * 100);
            bar.style.width = `${pct}%`;
            caption.innerText = `${count} / ${maxCapacity} seats filled`;
        } else {
            alert("This group expedition has already reached its seating limit constraints.");
        }
    }
}