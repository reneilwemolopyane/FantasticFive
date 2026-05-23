document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("packageForm");
    if (form) {
        form.addEventListener("submit", (e) => {
            const price = document.getElementById("price").value;
            const duration = document.getElementById("duration").value;
            
            if (parseFloat(price) <= 0 || parseInt(duration) <= 0) {
                alert("Please declare realistic and positive numeric values before updating.");
                e.preventDefault();
            }
        });
    }
});