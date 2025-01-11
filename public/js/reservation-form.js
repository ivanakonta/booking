document.addEventListener('DOMContentLoaded', function () {
    let currentUpdateTimeSlotsRequest = null;
    let currentLoadTableInfoRequest = null;
    const reservationForm = document.getElementById("reservation_form");
    let nonWorkingDays = JSON.parse(reservationForm.dataset.nonWorkingDays);
    let picker;

    document.getElementById('reservation_form_experience').addEventListener('change', (event) => {
        showExperienceDescription();
        loadFormTableInfo();
    });

    document.getElementById('reservation_form_table_persons')?.addEventListener('change', (event) => {
        updateFormTableTimeOptions();
    });

    $(document).ready(function () {
        $("#showTableInfo").click(function (event) {
            event.preventDefault();
            toggleReservationStep('tableInfo');
        });

        $("#showPersonalInfo").click(function (event) {
            event.preventDefault();
            toggleReservationStep('personalInfo');
        });

        $("#backToExperience").click(function (event) {
            event.preventDefault();
            toggleReservationStep('experienceInfo');
        });

        $("#backToTableInfo").click(function (event) {
            event.preventDefault();
            toggleReservationStep('tableInfo');
        });

        showCorrespondingReservationStep();
        initCalendar();

        $('.spinner-border').hide();

        $('#reservation_form').on('submit', function (event) {
            $('#button-form').prop('disabled', true);
            $('.spinner-border').show();
        });
    });

    // Switch Table and Guest (personal) view
    function toggleReservationStep(part) {
        const experienceInfo = document.getElementById('experienceInfo');
        const tableInfo = document.getElementById('tableInfo');
        const personalInfo = document.getElementById('personalInfo');

        if (part === 'experienceInfo' && experienceInfo) {
            experienceInfo.classList.remove('hidden');
            if (tableInfo) tableInfo.classList.add('hidden');
            if (personalInfo) personalInfo.classList.add('hidden');
        } else if (part === 'tableInfo' && tableInfo) {
            if (experienceInfo) experienceInfo.classList.add('hidden'); // Hide the experience step
            tableInfo.classList.remove('hidden');
            if (personalInfo) personalInfo.classList.add('hidden');
        } else if (part === 'personalInfo' && personalInfo) {
            if (experienceInfo) experienceInfo.classList.add('hidden'); // Hide the experience step
            personalInfo.classList.remove('hidden');
            if (tableInfo) tableInfo.classList.add('hidden');
        }
    }

    /**
     * Show corresponding step in case of error
     */
    function showCorrespondingReservationStep() {
        const reservationStep = getSmallestReservationStepWithError();

        if (reservationStep == 3) {
            toggleReservationStep('personalInfo');
        } else if (reservationStep == 2) {
            toggleReservationStep('tableInfo');
        } else if (reservationStep == 1) {
            toggleReservationStep('experienceInfo');
        }
    }

    /**
     * Retrieve the smallest reservation step that has an error.
     */
    function getSmallestReservationStepWithError() {
        let reservationStep = 99;

        document.querySelectorAll('.error-message').forEach((errorMessage) => {
            const errorStep = parseInt(errorMessage.dataset.step);

            if (errorStep < reservationStep) {
                reservationStep = errorStep;
            }
        });

        return reservationStep;
    }

    /**
     * Show experience descriptions after change selected experience.
     */
    function showExperienceDescription() {
        const selectExperiences = JSON.parse(reservationForm.dataset.selectExperiences);
        const formExperience = document.getElementById('reservation_form_experience');
        const experienceDescription = document.getElementById('experienceDescription');

        experienceDescription.innerHTML = selectExperiences[formExperience.value].description;
    }

    /**
     * Load Form Table data after change selected experience.
     */
    function loadFormTableInfo() {
        const personsElement = document.getElementById('reservation_form_table_persons');
        const tableDateElement = document.getElementById('reservation_form_table_date');
        const formExperience = document.getElementById('reservation_form_experience');

        // if (personsElement && tableDateElement && experienceOptionsElement) {
        const persons = personsElement.value;
        const tableDate = tableDateElement.value;
        const experienceId = formExperience.value;
        const tableInfoUrl = reservationForm.dataset.tableInfoUrl
            .replace('__experience__', experienceId)
            .replace('__date__', tableDate)
            .replace('__guests__', persons);

        // Send AJAX request to get the time slots
        currentLoadTableInfoRequest = $.ajax({
            url: tableInfoUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                updateCalendar(response.calendar);
                replaceTimeSlotOptions(response.timeOptionsHtml);
            },
            complete: function () {
                // Reset currentLoadTableInfoRequest
                currentLoadTableInfoRequest = null;
            }
        });
    }

    function updateFormTableTimeOptions() {
        // If there's an active request, abort it
        if (currentUpdateTimeSlotsRequest) {
            currentUpdateTimeSlotsRequest.abort();
        }

        const personsElement = document.getElementById('reservation_form_table_persons');
        const tableDateElement = document.getElementById('reservation_form_table_date');
        const formExperience = document.getElementById('reservation_form_experience');

        const persons = personsElement.value;
        const tableDate = tableDateElement.value;
        const experienceId = formExperience.value;
        const timeSlotsUrl = reservationForm.dataset.timeSlotsUrl
            .replace('__experience__', experienceId)
            .replace('__date__', tableDate)
            .replace('__guests__', persons);

        // Send AJAX request to get the time slots
        currentUpdateTimeSlotsRequest = $.ajax({
            url: timeSlotsUrl,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                replaceTimeSlotOptions(response.timeOptionsHtml);
            },
            complete: function () {
                // Reset currentUpdateTimeSlotsRequest
                currentUpdateTimeSlotsRequest = null;
            }
        });
    }

    function replaceTimeSlotOptions(timeOptionsHtml) {
        const timeElement = document.getElementById("reservation_form_table_time");

        if ('' == timeOptionsHtml) {
            const divError = document.createElement("div");
            divError.classList.add('error-message');
            divError.classList.add('error-time-slot');
            divError.innerText = "There is no free Time Slots";
            timeElement.parentElement.appendChild(divError);
        } else {
            document.querySelectorAll(".error-time-slot").forEach(el => el.remove());

            const selectedTimeSlot = timeElement.value;
            timeElement.innerHTML = timeOptionsHtml;
            timeElement.value = selectedTimeSlot;
        }
    }

    function initCalendar() {
        const datePickerField = document.getElementById('datepicker');
        const firstCalendarDate = new Date(reservationForm.dataset.firstCalendarDate);
        const lastCalendarDate = new Date(reservationForm.dataset.lastCalendarDate);
        const selectedDate = new Date(reservationForm.dataset.selectedDate);

        picker = new Pikaday({
            field: datePickerField,
            format: 'MMMM D, YYYY', // Format like "December 10, 2023"
            defaultDate: selectedDate, // Set default date to today
            minDate: firstCalendarDate,
            setDefaultDate: true, // Display the default date in the input field on load
            maxDate: lastCalendarDate,
            disableDayFn: disableCalendarDay,
            toString(date, format) {
                // Format the date into a string
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            },
            parse(dateString, format) {
                // Parse the date string back into a date object
                const parts = dateString.match(/(\d+)/g);
                let day, month, year;

                if (parts && parts.length === 3) {
                    day = parseInt(parts[1], 10);
                    month = parseInt(parts[0], 10) - 1; // JavaScript months are 0-based
                    year = parseInt(parts[2], 10);
                }

                return new Date(year, month, day);
            },
            onSelect: function (date) {
                updateFormTableDate(date);
                updateFormTableTimeOptions();
            }
        });

        picker.setDate(selectedDate);
    }

    function disableCalendarDay(date) {
        // console.log(nonWorkingDays);
        const formattedDate = new Date(date.getTime() - (date.getTimezoneOffset() * 60000)).toISOString().split('T')[0];
        return nonWorkingDays.includes(formattedDate);
    };

    function updateCalendar(calendar) {
        nonWorkingDays = calendar.nonWorkingDays;
        picker.setMinDate(new Date(calendar.firstCalendarDate));
        picker.setMaxDate(new Date(calendar.lastCalendarDate));
    }

    function updateFormTableDate(date) {
        const tableDate = document.getElementById('reservation_form_table_date');
        if (tableDate) {
            const formattedDate = convertDateToYmdFormat(date);
            tableDate.value = formattedDate;
        }
    }

    function convertDateToYmdFormat(date) {
        const dd = date.getDate();
        const mm = (date.getMonth() + 1);
        const DD = dd < 10 ? ('0' + dd) : dd;
        const MM = mm < 10 ? ('0' + mm) : mm;
        const formattedDate = date.getFullYear() + '-' + MM + '-' + DD;

        return formattedDate;
    }

    // Disable button form
    const form = document.querySelector('.form');
    const submitButton = document.getElementById('button-form');

    if (form && submitButton) {
        function checkRequiredFields() {
            let allFilled = true;
            form.querySelectorAll('input[required], textarea[required]').forEach((input) => {
                if (!input.value.trim()) {
                    allFilled = false;
                }
            });
            submitButton.disabled = !allFilled;
        }

        // Event listener for form inputs
        form.querySelectorAll('input[required], textarea[required]').forEach((input) => {
            input.addEventListener('input', checkRequiredFields);
        });

        // Initial check on page load
        checkRequiredFields();
    }
});