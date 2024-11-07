<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JayWing Academy - Tutor Management System</title>
    
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- custom css -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <?php include '../components/navbar.php'; ?>
    </header>

    <main>
        <!-- TODO: Jobs page goes here -->
         <div class="center-container">
         <form class="row g-3 needs-validation" novalidate id="tutorApplicationForm">

         <div class="col-md-4 position-relative">
    <label for="validationTooltip01" class="form-label">Course Code</label>
    <input 
        type="text" 
        class="form-control" 
        id="validationTooltip01" 
        required 
        pattern="[A-Za-z]{2,3}\d{3,}" 
        title="Course code must start with 2-3 letters followed by at least 3 numbers"
        oninput="validateCourseCode()"
    >
    <div class="valid-tooltip">
      Looks good!
    </div>
    <div class="invalid-tooltip">
      Course code must start with 2-3 letters followed by at least 3 numbers.
    </div>
</div>





  <div class="col-md-4">
    <label for="validationTooltipUsername" class="form-label">Professor Email</label>
    <div class="input-group has-validation">
      <input 
        type="text" 
        class="form-control" 
        id="validationTooltipUsername" 
        aria-describedby="validationTooltipUsernameAppend" 
        required 
        oninput="validateEmailInput(this)"
      >
      <span class="input-group-text" id="validationTooltipUsernameAppend">@etown.edu</span>
      <div class="invalid-tooltip">
        Please choose a valid email.
      </div>
    </div>
</div>

<div class="col-12">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="" id="invalidCheck2">
      <label class="form-check-label" for="invalidCheck2">
        <p>By checking this box, I confirm that I have read and agreed to the terms and conditions of this application.</p>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#termsModal">
          View Terms and Conditions
        </button>
      </label>
    </div>
  </div>

<!-- Terms and Agreemnts Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Insert your terms and conditions text here -->
        <p><strong>Terms and Conditions for Tutor Application</strong></p>
  
  <p><strong>1. Consent to Share Academic Information</strong><br>
  You acknowledge that applying for this tutoring position requires you to share information about your academic background, including but not limited to your educational history, certifications, and any academic achievements. By providing this information, you grant permission for this data to be reviewed as part of the application process.</p>
  
  <p><strong>2. FERPA Compliance</strong><br>
  You understand that by voluntarily sharing your academic information, you are consenting to its use in accordance with FERPA (Family Educational Rights and Privacy Act) guidelines. This disclosure is necessary for evaluating your qualifications as a tutor and will not be shared outside of authorized review personnel.</p>
  
  <p><strong>3. Data Privacy</strong><br>
  Your personal and academic information will be handled securely and only accessed by individuals directly involved in the application review process.</p>
  
  <p><strong>4. Agreement to Terms</strong><br>
  By proceeding with the application, you affirm that you have read and understood these terms and consent to the described use of your academic information.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

  


  
<!-- script to check and make sure terms is checked -->
<script>
  const form = document.getElementById("tutorApplicationForm");
const checkbox = document.getElementById("invalidCheck2");
const submitBtn = document.getElementById("submitButton");

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
</script>



<!-- Valid email check -->
<script>
function validateEmailInput(input) {
    // Allowed characters: letters, numbers, ., _, %, +, and -
    input.value = input.value.replace(/[^a-zA-Z0-9._%+-]/g, '');
}
</script>


  <div class="mb-3">
    <label for="validationTextarea" class="form-label">Textarea</label>
    <textarea class="form-control" id="validationTextarea" placeholder="Note for Professor" required></textarea>
    <div class="invalid-feedback">
      Please enter a message in the textarea.
    </div>
  </div>

  <div class="col-12">
  <button type="submit" id="submitButton" class="btn btn-primary" disabled>Submit</button>


<!-- Making sure the course codes are valid -->
<script>
function validateCourseCode() {
    const courseCodeInput = document.getElementById('validationTooltip01');
    const submitButton = document.getElementById('submitButton');
    const courseCodePattern = /^[A-Za-z]{2,3}\d{3,}$/;

    // Enable submit button only if input matches pattern
    if (courseCodePattern.test(courseCodeInput.value)) {
        submitButton.disabled = false;
    } else {
        submitButton.disabled = true;
    }
}
</script>
  </div>
  
</form>



    </main>
    
    <footer id="footer"></footer>

    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>
</body>
</html>