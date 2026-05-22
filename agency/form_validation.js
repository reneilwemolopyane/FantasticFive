document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.package-form');
    const submitBtn = document.querySelector('.btn-submit');

    form.addEventListener('submit', (e) => {
        const priceInput = form.querySelector('input[name="price"]');
        const durationInput = form.querySelector('input[name="duration"]');

        // Prevent negative values
        if (parseFloat(priceInput.value) <= 0 || parseInt(durationInput.value) <= 0) {
            e.preventDefault();
            alert('Price and Duration must be greater than zero.');
            return;
        }

        // Prevent double submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating Package...';
    });
});
