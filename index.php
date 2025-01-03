<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JayWing Academy - Tutor Management System</title>
    
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- custom css -->
    <link rel="stylesheet" href="src/css/style.css">
</head>
<body>
    <header>
        <?php include 'src/components/navbar.php'; ?>
    </header>

    <main>
        <div class="center-container">

            <section id="showcase">
                <div class="container">
                    <h1>Welcome to JayWing Academy</h1>
                    <p>Connecting students with tutors for personalized learning experiences</p>
                </div>
            </section>
        
            <section id="features">
                <div class="container">
                    <div class="feature">
                        <h3>Schedule System</h3>
                        <p>Complete personalized calendar page, along with appointment requests</p>
                    </div>
                    <div class="feature">
                        <h3>Tutor Requests</h3>
                        <p>Search and request tutors based on subject, availability, and ratings</p>
                    </div>
                    <div class="feature">
                        <h3>Wings Collection</h3>
                        <p>Gamified system to encourage student participation and engagement</p>
                    </div>
                    <div class="feature">
                        <h3>Private Messaging</h3>
                        <p>Secure private messaging between users</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
    
    <footer id="footer"></footer>

    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom JS -->
    <script src="src/js/global.js"></script>
</body>
</html>