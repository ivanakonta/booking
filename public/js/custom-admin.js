function updateReservationBadgeCounter() {
    const reservationBadge = document.getElementById('reservationBadge');

    if (reservationBadge) {
        const reservationCounterUrl = reservationBadge.dataset.reservationCounterUrl;

        // Send AJAX request to check the uniqueness of the slug
        $.ajax({
            url: reservationCounterUrl,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                const reservationBadgeLink = document.getElementById('reservationBadgeLink');
                const reservationBadgeCounter = document.getElementById('reservationBadgeCounter');
                reservationBadgeLink.dataset.bsContent = response.message;
                reservationBadgeCounter.innerHTML = response.counter;

                if (response.counter > 0) {
                    reservationBadgeLink.classList.remove("hidden");
                    $('[data-bs-toggle="popover"]').popover();
                }
            },
            complete: function() {
            }
        });
    }
}

$(document).ready(function () {
    updateReservationBadgeCounter();
});

// $(function () {
//     $('[data-bs-toggle="popover"]').popover();
// })
