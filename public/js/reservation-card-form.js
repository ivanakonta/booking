/* disable card button */
document.addEventListener('DOMContentLoaded', function () {
    const cardForm = document.querySelector('.card-form');
    const reserveButton = document.getElementById('button-card');
    const termsCheckbox = cardForm.querySelector('.checkbox');

    reserveButton.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default form submission
        if (!this.disabled) {
            window.location.href = './step-4.html';
        }
    });

    function checkRequiredFields() {
        let allFilled = true;
        cardForm.querySelectorAll('input[required]').forEach((input) => {
            if (!input.value.trim()) {
                allFilled = false;
            }
        });

        // Also check if the terms checkbox is checked
        if (!termsCheckbox.checked) {
            allFilled = false;
        }

        reserveButton.disabled = !allFilled;
    }

    // Event listeners for form inputs and the checkbox
    cardForm.querySelectorAll('input[required], .checkbox').forEach((input) => {
        input.addEventListener('input', checkRequiredFields);
    });

    // Initial check
    checkRequiredFields();
});