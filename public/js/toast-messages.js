(function ($) {
    "use strict";

    // Function to create Toastr notification
    function createToastr(type, message, title) {

        toastr[type](message, title, {
            timeOut: 500000000,
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: true,
            onclick: null,
            showDuration: "0", // Set show duration to 0 for instant display
            hideDuration: "0", // Set hide duration to 0 for instant disappearance
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
            tapToDismiss: false
        });
    }

    // Handle flash-success messages
    $(".flash-success").each(function(i, obj) {
        createToastr('success', obj.innerText, obj.dataset.title);
    });

    // Handle flash-info messages
    $(".flash-info").each(function(i, obj) {
        createToastr('info', obj.innerText, obj.dataset.title);
    });

    // Handle flash-warning messages
    $(".flash-warning").each(function(i, obj) {
        createToastr('warning', obj.innerText, obj.dataset.title);
    });

    // Handle flash-danger messages
    $(".flash-danger").each(function(i, obj) {
        createToastr('error', obj.innerText, obj.dataset.title);
    });

    // Handle flash-danger messages
    $(".flash-error").each(function(i, obj) {
        createToastr('error', obj.innerText, obj.dataset.title);
    });
})(jQuery);