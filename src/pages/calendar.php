<?php
require_once '../data_src/includes/session_handler.php';
require_once '../data_src/includes/db_connect.php';

$isUserLoggedIn = isLoggedIn();
$userEmail = '';
$userId = '';

// Fetch event types from database
$eventTypes = [];
if ($isUserLoggedIn) {  // Only fetch if logged in
    $userId = $_SESSION['user_id'];
    $stmt = $connection->prepare("SELECT email FROM User WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userEmail = $row['email'];
    }
    $stmt->close();

    $result = $connection->query("SELECT * FROM Event_Type");
    while($row = $result->fetch_assoc()) {
        $eventTypes[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JayWing Academy - Calendar</title>
    
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet'>
    
    <!-- custom css -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <?php include '../components/navbar.php'; ?>
    </header>

    <main>
        <div class="container mt-4">
            <div id='calendar'></div>
        </div>

        <?php if ($isUserLoggedIn): ?>
        <!-- Event Creation Modal -->
        <div class="modal fade" id="createEventModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createEventForm" method="POST">
                            <div class="mb-3">
                                <label for="eventName" class="form-label">Event Name</label>
                                <input type="text" class="form-control bg-dark text-white" id="eventName" name="event_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control bg-dark text-white" id="location" name="location" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="startDateTime" class="form-label">Start Date/Time</label>
                                    <input type="datetime-local" class="form-control bg-dark text-white" id="startDateTime" name="start_time" required>
                                </div>
                                <div class="col">
                                    <label for="endDateTime" class="form-label">End Date/Time</label>
                                    <input type="datetime-local" class="form-control bg-dark text-white" id="endDateTime" name="end_time" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="eventType" class="form-label">Event Type</label>
                                <select class="form-select bg-dark text-white" id="eventType" name="event_type_id" required>
                                    <?php foreach ($eventTypes as $type): ?>
                                        <option value="<?= htmlspecialchars($type['event_type_id']) ?>">
                                            <?= htmlspecialchars($type['type_name']) ?> (<?= $type['wings'] ?> Wings)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Participants (Email)</label>
                                <div id="participantsList">
                                    <div class="participant-entry row mb-2">
                                        <div class="col-md-8">
                                            <input type="email" class="form-control bg-dark text-white participant-email" 
                                                value="<?php echo htmlspecialchars($userEmail); ?>" 
                                                name="participants[0][email]" 
                                                required 
                                                readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <select class="form-select bg-dark text-white" name="participants[0][role]" required>
                                                <option value="professor">Professor</option>
                                                <option value="tutor">Tutor</option>
                                                <option value="tutee">Tutee</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-secondary mt-2" onclick="addParticipantField()">
                                    Add Participant
                                </button>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Create Event</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Event Details Modal -->
        <div class="modal fade" id="eventDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title">Event Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="eventDetails">
                            <h4 id="detailsEventTitle"></h4>
                            <p><strong>Location:</strong> <span id="detailsEventLocation"></span></p>
                            <p><strong>Time:</strong> <span id="detailsEventTime"></span></p>
                            <p><strong>Type:</strong> <span id="detailsEventType"></span></p>
                            <p><strong>Wings:</strong> <span id="detailsEventWings"></span></p>
                            
                            <h5>Participants</h5>
                            <div id="detailsEventParticipants" class="table-responsive">
                                <table class="table table-dark">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody id="participantsTableBody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="eventActions" class="mt-3">
                            <!-- Edit button will be added here dynamically if user is creator -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
    

    
    <footer id="footer"></footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    
    <!-- Pass login status to JavaScript -->
    <script>
        const isLoggedIn = <?php echo json_encode($isUserLoggedIn); ?>;
        const currentUserEmail = <?php echo json_encode($userEmail); ?>;
    </script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>
    <script src="../js/calendar.js"></script>
</body>
</html>