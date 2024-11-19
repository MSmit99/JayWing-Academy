<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Our Team</title>

    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- custom css -->
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body id="aboutUsBody"> 
  <header>
    <?php include '../../components/navbar.php'; ?>
  </header>  

  <div class="container" id="aboutUsContainer">
        <header id="aboutUsHeader">
            <h1 id="aboutUsTitle">Meet Our Team</h1>
            <p class="subtitle">Passionate professionals working together to achieve excellence</p>
        </header>

        <div class="team-grid">
            <div class="team-member">
              <h2 class="member-name">Muz Islam</h2>
              <p class="member-role">Daddy Muz</p>
              <p class="member-bio">Muz is a senior IS major actively looking for a job when he graduates.</p>
              <div class="btn-container aboutBtnContainer">
                <a href="islam.html" class="btn aboutBtn">View Profile</a>
              </div>
            </div>
          
            <div class="team-member">
              <h2 class="member-name">Alex Roop</h2>
              <p class="member-role">Programmer</p>
              <p class="member-bio">Alex is a junior data science major looking for an internship in the upcoming summer.</p>
              <div class="btn-container aboutBtnContainer">
                <a href="roop.html" class="btn aboutBtn">View Profile</a>
              </div>
            </div>
          
            <div class="team-member">
              <h2 class="member-name">Joey Wagner</h2>
              <p class="member-role">Programmer</p>
              <p class="member-bio">Joey is a junior data science major looking for an internship in the upcoming summer.</p>
              <div class="btn-container aboutBtnContainer">
                <a href="wagner.html" class="btn aboutBtn">View Profile</a>
              </div>
            </div>
          
            <div class="team-member">
              <h2 class="member-name">Matt Smith</h2>
              <p class="member-role">Programmer</p>
              <p class="member-bio">Matt is a junior cybersecurity and data science major looking for an internship in the upcoming summer.</p>
              <div class="btn-container aboutBtnContainer">
                <a href="smith.html" class="btn aboutBtn">View Profile</a>
              </div>
            </div>
        </div>
    </div>

    <footer id="footer"></footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="../../js/global.js"></script>
</body>
</html>