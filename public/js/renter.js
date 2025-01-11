document.addEventListener("DOMContentLoaded", function() {
    var titleInput = document.querySelector('[name="renter_form[name]"]');
    var slugInput = document.querySelector('[name="renter_form[slug]"]');
    // var reservationSlugElement = document.getElementById('reservation-slug');
    var slugErrorElement = document.getElementById('slug-error');
    var submitButton = document.getElementById('submit-button'); // Assuming the ID of your submit button
    var currentChangeTitleRequest = null;
    var currentChangeSlugRequest = null;
    var currentSlug = slugInput.value;

    // Event listener for blur on title input field
    titleInput.addEventListener('blur', function() {
        // Ako postoji aktivni zahtjev, prekini ga
        if (currentChangeTitleRequest) {
            currentChangeTitleRequest.abort();
        }

        var title = titleInput.value;

        // Send AJAX request to check the uniqueness of the slug
        currentChangeTitleRequest = $.ajax({
            url: '/renter/ajax/check-unique-slugs',
            method: 'POST',
            dataType: 'json',
            data: { name: title },
            success: function(response) {
                // Update Slug input field
                slugInput.value = response.slug;

                // Display error message if slug is not unique and not have same value
                if (!response.isSlugUnique && currentSlug != response.slug) {
                    slugErrorElement.textContent = 'Slug is taken. Please edit the slug.';
                    submitButton.disabled = true; // Disable form submission button
                } else {
                    slugErrorElement.textContent = '';
                    submitButton.disabled = false; // Enable form submission button
                }
            },
            complete: function() {
                // Reset currentChangeTitleRequest
                currentChangeTitleRequest = null;
            }
        });
    });

    // Event listener for manual edit of slug input field
    slugInput.addEventListener('input', function() {
        // Ako postoji aktivni zahtjev, prekini ga
        if (currentChangeSlugRequest) {
            currentChangeSlugRequest.abort();
        }

        // Clear any error message when user starts editing the slug manually
        slugErrorElement.textContent = '';

        // Check for unique slug when user edits the slug directly
        var editedSlug = this.value.trim(); // Trim to remove leading/trailing spaces
        if (editedSlug !== '') {
            // Send AJAX request to check the uniqueness of the edited slug
            currentChangeSlugRequest = $.ajax({
                url: '/renter/ajax/check-unique-slugs',
                method: 'POST',
                dataType: 'json',
                data: { name: editedSlug }, // Send the edited slug as the title
                success: function(response) {
                    // Display error message if edited slug is not unique and not have same value
                    if (!response.isSlugUnique && currentSlug != response.slug) {
                        slugErrorElement.textContent = 'Slug is taken. Please edit the slug.';
                        submitButton.disabled = true; // Disable form submission button
                    } else {
                        // Update reservation slug if the edited slug is unique
                        submitButton.disabled = false; // Enable form submission button
                    }
                },
                complete: function() {
                    // Reset currentChangeSlugRequest
                    currentChangeSlugRequest = null;
                }
            });
        }
    });

    // Event listener for form submission
    document.getElementById('renter-form').addEventListener('submit', function(event) {
        if (!slugInput.value.trim() || slugErrorElement.textContent !== '') {
            // Prevent form submission if slug is empty or not unique
            event.preventDefault();
        }
    });
});
