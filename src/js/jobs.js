document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById("tutorApplicationForm");
    const checkbox = document.getElementById("invalidCheck2");
    const submitBtn = document.getElementById("submitButton");
    const courseCodeInput = document.getElementById('validationTooltip01');

    // Validate course code
    function validateCourseCode() {
        const courseCodePattern = /^[A-Za-z]{2,3}\d{3,}$/;

        // Enable submit button only if input matches pattern
        if (courseCodePattern.test(courseCodeInput.value)) {
            submitBtn.disabled = false;
        } else {
            submitBtn.disabled = true;
        }
    }

    // Validate email input
    function validateEmailInput(input) {
        // Allowed characters: letters, numbers, ., _, %, +, and -
        input.value = input.value.replace(/[^a-zA-Z0-9._%+-]/g, '');
    }

    // Add event listener to toggle submit button based on checkbox state
    checkbox.addEventListener('change', function() {
        submitBtn.disabled = !checkbox.checked; // Disable submit if checkbox is unchecked
    });

    // Prevent form submission if checkbox is not checked
    form.addEventListener("submit", function(event) {
        if (!checkbox.checked) {
            event.preventDefault();  // Prevent form submission
            alert("You must agree to the terms and conditions before submitting the application.");
        }
    });

    // Attach event listeners
    courseCodeInput.addEventListener('input', validateCourseCode);
    document.getElementById('validationTooltipUsername').addEventListener('input', function() {
        validateEmailInput(this);
    });
});