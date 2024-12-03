<?php
require_once '../data_src/includes/session_handler.php';
require_once '../data_src/includes/db_connect.php';

// Ideally user is always logged in when they reach this page
if (!isLoggedIn()) {
  // TODO: Add login popup here or before page is loaded
}

// SQL Queries
// TODO: Combine some of the queries into one query to reduce the number of queries
// Getting User Credentials
$user_id = getCurrentUserId();
$user = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT username, firstName, lastName, email, wings, Unavailable FROM User WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();
}

// Getting User Titles
$user_titles = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT DISTINCT roleOfClass FROM Enrollment WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user_titles = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

// TODO: Combine "Top 5" and "All" queries into one query to reduce the number of queries
// Getting Top 5 Upcoming Events
$events = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT e.event_id, e.eventName, e.eventStartTime, e.eventEndTime, e.Location, e.eventDescription FROM Event e 
                                JOIN Attendance a ON e.event_id = a.event_id
                                WHERE a.user_id = ?
                                AND e.eventStartTime > NOW()
                                ORDER BY e.eventStartTime ASC
                                LIMIT 5;");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $events = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

// Getting All Upcoming Events
$all_events = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT e.event_id, e.eventName, e.eventStartTime, e.eventEndTime, e.Location, e.eventDescription FROM Event e 
                                JOIN Attendance a ON e.event_id = a.event_id
                                WHERE a.user_id = ?
                                AND e.eventStartTime > NOW()
                                ORDER BY e.eventStartTime ASC;");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $all_events = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

// Check if events are greater than five
$more_events = false;

if ($events && $all_events) {
  $more_events = count($events) < count($all_events);
}

// Getting Top 5 Classes User is Tutoring For
$classes = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT c.class_id, c.className, c.courseCode, c.classDescription, AVG(pr.personRating) AS averageRating, COUNT(pr.rating_id) AS ratingCount
                                FROM Class c
                                JOIN Enrollment e ON c.class_id = e.class_id
                                LEFT JOIN Person_Rating pr ON e.class_id = pr.class_id AND e.user_id = pr.tutor_id
                                WHERE e.user_id = ? AND e.roleOfClass = 'Tutor'
                                GROUP BY c.class_id, c.className, c.courseCode, c.classDescription
                                ORDER BY ratingCount DESC
                                LIMIT 5;");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $classes = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

// Getting All Classes User is Tutoring For
$all_classes = null;

if ($user_id) {
  $stmt = $connection->prepare("SELECT c.class_id, c.className, c.courseCode, c.classDescription, AVG(pr.personRating) AS averageRating, COUNT(pr.rating_id) AS ratingCount
                                FROM Class c
                                JOIN Enrollment e ON c.class_id = e.class_id
                                LEFT JOIN Person_Rating pr ON e.class_id = pr.class_id AND e.user_id = pr.tutor_id
                                WHERE e.user_id = ? AND e.roleOfClass = 'Tutor'
                                GROUP BY c.class_id, c.className, c.courseCode, c.classDescription
                                ORDER BY ratingCount DESC;");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $all_classes = $result->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
}

// Check if classes tutoring for is greater than five
$more_classes = false;

if ($classes && $all_classes) {
  $more_classes = count($classes) < count($all_classes);
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
              <!-- Maybe add this back in the event that we allow users to view other users profile page -->
              <!-- <button  type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-primary ms-1">Message</button> -->
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
                <p class="text-muted mb-0">
                  <?php echo htmlspecialchars($user['firstName'] ?? 'First Name'); ?>
                  <?php echo htmlspecialchars($user['lastName'] ?? 'Last Name'); ?>
                </p>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-sm-3">
                <p class="mb-0">Email</p>
              </div>
              <div class="col-sm-9">
                <p class="text-muted mb-0"><?php echo htmlspecialchars($user['email'] ?? 'Email Address'); ?></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottom Row -->
        <div class="row">
          <!-- Bottom Row Left Side -->
           <!-- Upcoming Events -->
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
                    <!-- If there are more events than the top 5 -->
                    <?php if ($more_events) : ?>
                      <div class="d-flex justify-content-end mt-2">
                        <button type="button" class="btn btn-outline-primary">View All</button>
                      </div>
                    <?php endif; ?>
                    <!-- If no events scheduled -->
                  <?php else : ?>
                    No Upcoming Events
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          </div>

          <!-- Bottom Row Right Side -->
          <!-- Classes User is tutoring for -->
          <div class="col-md-6">
            <div class="card mb-4 mb-md-0">
              <div class="card-body">
                <p class="mb-4"><span class="text-primary font-italic me-1">Tutoring For</span></p>
                <!-- Display Top 5 Classes Tutoring For with the number of ratings and average rating -->
                <ul class="list-group">
                <?php if ($classes) : ?>
                  <?php foreach ($classes as $class) : ?>
                    <li class="list-group upcoming-event-item">
                      <div class="d-flex justify-content-between">
                        <h6 class="mb-1"><?php echo htmlspecialchars($class['className']); ?></h6>
                        <small><?php echo htmlspecialchars($class['courseCode']); ?></small>
                      </div>
                      <small>
                        <?php 
                          $ratingCount = (int)$class['ratingCount'];
                          echo $ratingCount . ' ' . ($ratingCount === 1 ? 'Rating' : 'Ratings'); 
                        ?>
                        <?php if ($class['averageRating']) : ?>
                          - <?php echo number_format((float)$class['averageRating'], 2); ?> Stars
                        <?php else : ?>
                            <!-- Placeholder for no ratings -->
                          <?php endif; ?>
                      </small>
                    </li>
                  <?php endforeach; ?>
                <?php else : ?>
                  No Classes
                <?php endif; ?>
                </ul>
                <!-- If there are more classes than the top 5 -->
                <?php if ($more_classes) : ?>
                  <div class="d-flex justify-content-end mt-2">
                    <button type="button" class="btn btn-outline-primary">View All</button>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <!-- Availability Section -->
          <div class="row mt-4">
          <div class="col-md-12">
            <div class="card mb-4 mb-md-0">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <p class="mb-0"><span class="text-primary font-italic me-1">Availability Settings</span></p>
                  <button type="button" 
                          class="btn <?php echo $user['Unavailable'] ? 'btn-success' : 'btn-danger'; ?>" 
                          id="toggleAvailability">
                    <?php echo $user['Unavailable'] ? 'Set Available' : 'Set Unavailable'; ?>
                  </button>
                </div>

                <!-- Current Availability Display -->
                <div class="mb-4" id="currentAvailability">
                  <h6 class="mb-3">Current Availability</h6>
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Day</th>
                          <th>Start Time</th>
                          <th>End Time</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody id="availabilityTableBody">
                        <!-- Will be populated by JavaScript -->
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- Availability Form -->
                <form id="availabilityForm">
                  <h6 class="mb-3">Set New Availability</h6>
                  <div id="availabilityInputs">
                    <div class="row mb-3 availability-entry">
                      <div class="col-md-4">
                        <select class="form-select" name="weekday[]" required>
                          <option value="">Select Day</option>
                          <option value="MONDAY">Monday</option>
                          <option value="TUESDAY">Tuesday</option>
                          <option value="WEDNESDAY">Wednesday</option>
                          <option value="THURSDAY">Thursday</option>
                          <option value="FRIDAY">Friday</option>
                          <option value="SATURDAY">Saturday</option>
                          <option value="SUNDAY">Sunday</option>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <input type="time" class="form-control" name="start[]" required>
                      </div>
                      <div class="col-md-3">
                        <input type="time" class="form-control" name="end[]" required>
                      </div>
                      <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-time">Remove</button>
                      </div>
                    </div>
                  </div>
                  <button type="button" class="btn btn-secondary mb-3" id="addTimeSlot">Add Time Slot</button>
                  <button type="submit" class="btn btn-primary w-100">Save Availability</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
<!-- TODO: Add a calendar feature at the bottom that allows user to update their availability -->

</section>

    </main>
    
    <footer id="footer"></footer>

    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>
    <script src="../js/availability.js"></script>
</body>
</html>