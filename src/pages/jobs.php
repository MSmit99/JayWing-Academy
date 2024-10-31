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
         <form class="row g-3 needs-validation" novalidate>

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
         </div>

    </main>
    
    <footer id="footer"></footer>

    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>
</body>
</html>