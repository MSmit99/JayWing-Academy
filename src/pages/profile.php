<?php
require_once '../data_src/includes/session_handler.php';
require_once '../data_src/includes/db_connect.php';

// Ideally user is always logged in when they reach this page
if (!isLoggedIn()) {
  // TODO: Add login popup here or before page is loaded
}

// Getting User Credentials
$user_id = getCurrentUserId();
$user = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT username, email, wings FROM user WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();
}

// Getting User Titles
$user_titles = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT DISTINCT roleOfClass FROM enrollment WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user_titles = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

// Getting Upcoming Events
$events = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT e.event_id, e.eventName, e.eventStartTime, e.eventEndTime, e.Location, e.eventDescription FROM event e 
                                JOIN attendance a ON e.event_id = a.event_id
                                WHERE a.user_id = ?
                                AND e.eventStartTime > NOW()
                                ORDER BY e.eventStartTime ASC;");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $events = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}
?> 

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

    <style>
      .upcoming-event-item {
      margin-bottom: 10px; /* Adjust the value for more or less space */
      }
    </style>

</head>
<body>
    <header>
        <?php include '../components/navbar.php'; ?>
    </header>

    <main>
        <!-- TODO: Profile page goes here -->
         <!-- <div class="center-container">
            <h1>Profile</h1>
         </div> -->

         <!-- Bootstrap Link: https://mdbootstrap.com/docs/standard/extended/profiles/ -->

         <section style="background-color: #eee;">
         <div class="container py-5">
    <div class="row">
      <div class="col">
        
      </div>
    </div>

    <div class="row">
      <!-- Upper Left Profile Section -->
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body text-center">
          <img src="../images/blue_jay.png" alt="avatar"
          class="rounded-circle img-fluid" style="width: 125px;">
            <h5 class="my-3"><?php echo htmlspecialchars($user['username'] ?? 'Profile Name'); ?></h5>
            <p class="text-muted mb-1">
              <?php 
                // Display each of the users roles in a comma separated list
                if ($user_titles) {
                  $roleOfClass = array_column($user_titles, 'roleOfClass');
                  echo implode(', ', $roleOfClass);
                } else {
                  echo 'Current Positions';
                }
              ?>
            </p>
            <div class="d-flex justify-content-center mb-2">
              <button  type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-primary ms-1">Message</button>
            </div>
          </div>
        </div>

        <!-- Lower Left Profile Section -->
        <div class="card mb-4 mb-lg-0">
          <div class="card-body text-center">
            <!-- Wing Icon Section with Text Overlay -->
             <div style="position: relative; display: inline-block;">
             <h5 class="my-3">Wings Bank</h5>
             <img src="../images/wing.png" alt="Wings Icon" style="width: 200px; height: 200px;">
             <h5 class="my-3">Current Balance: <?php echo htmlspecialchars($user['wings'] ?? 0); ?></h5>              
            </div>
          </div>
        </div>
      </div>

      <!-- Upper Middle Profile Section -->
      <div class="col-lg-8">
        <div class="card mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Full Name</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">Johnatan Smith</p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Email</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">example@example.com</p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Phone</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">(097) 234-5678</p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Mobile</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">(098) 765-4321</p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Address</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0">Bay Area, San Francisco, CA</p>
              </div>
            </div>
          </div>
        </div>
        <!-- Bottom Row -->
        <div class="row">
          <!-- Bottom Row Left Side -->
          <div class="col-md-6">
            <div class="card mb-4 mb-md-0">
              <div class="card-body">
                <p class="mb-4"><span class="text-primary font-italic me-1">Upcoming Events</span>
                </p>
                <!-- List 5 Upcoming Events with soonest at top -->
                <ul class="list-group list-group-flush">

                <!-- If the User has events scheduled -->
                  <?php if ($events) : ?>
                    <?php foreach ($events as $event) : ?>
                      <li class="list-group upcoming-event-item">
                        <div class="d-flex justify-content-between">
                          <h6 class="mb-1"><?php echo htmlspecialchars($event['eventName']); ?></h6>
                          <small><?php echo htmlspecialchars($event['eventStartTime']); ?></small>
                        </div>
                        <p class="mb-1"><?php echo htmlspecialchars($event['eventDescription']); ?></p>
                        <small><?php echo htmlspecialchars($event['Location']); ?></small>
                      </li>
                    <?php endforeach; ?>
                    <!-- If no events scheduled -->
                  <?php else : ?>
                    No Upcoming Events
                  <?php endif; ?>
                </ul>
                
                
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card mb-4 mb-md-0">
              <div class="card-body">
                <p class="mb-4"><span class="text-primary font-italic me-1">Tutoring For</span>
                </p>
                <p class="mb-1" style="font-size: .77rem;">Web Design</p>
                <div class="progress rounded" style="height: 5px;">
                  <div class="progress-bar" role="progressbar" style="width: 80%" aria-valuenow="80"
                    aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-4 mb-1" style="font-size: .77rem;">Website Markup</p>
                <div class="progress rounded" style="height: 5px;">
                  <div class="progress-bar" role="progressbar" style="width: 72%" aria-valuenow="72"
                    aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-4 mb-1" style="font-size: .77rem;">One Page</p>
                <div class="progress rounded" style="height: 5px;">
                  <div class="progress-bar" role="progressbar" style="width: 89%" aria-valuenow="89"
                    aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-4 mb-1" style="font-size: .77rem;">Mobile Template</p>
                <div class="progress rounded" style="height: 5px;">
                  <div class="progress-bar" role="progressbar" style="width: 55%" aria-valuenow="55"
                    aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p class="mt-4 mb-1" style="font-size: .77rem;">Backend API</p>
                <div class="progress rounded mb-2" style="height: 5px;">
                  <div class="progress-bar" role="progressbar" style="width: 66%" aria-valuenow="66"
                    aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

    </main>
    
    <footer id="footer"></footer>

    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>
</body>
</html>