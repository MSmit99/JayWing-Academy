<?php
require_once __DIR__ . '/../data_src/includes/session_handler.php';
require_once __DIR__ . '/../data_src/includes/db_connect.php';

if (!isLoggedIn()) {
    header('Location: /jaywing-academy/index.php');
    exit();
}

$event_id = filter_input(INPUT_GET, 'event_id', FILTER_VALIDATE_INT);
if (!$event_id) {
    header('Location: calendar.php');
    exit();
}

$stmt = $connection->prepare("
    SELECT 1 FROM Attendance 
    WHERE event_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
$stmt->execute();
$isParticipant = $stmt->get_result()->num_rows > 0;

// Fetch event details
$stmt = $connection->prepare("
    SELECT e.*, et.eventTypeName as type_name, et.wings,
           a.user_id as creator_id
    FROM Event e
    JOIN Event_Type et ON e.type_id = et.event_type_id
    JOIN Attendance a ON e.event_id = a.event_id AND a.isCreator = 1
    WHERE e.event_id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

// Set isCreator using the Attendance table
$stmt = $connection->prepare("
    SELECT 1 FROM Attendance 
    WHERE event_id = ? AND user_id = ? AND isCreator = 1
");
$stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
$stmt->execute();
$isCreator = $stmt->get_result()->num_rows > 0;

// Fetch ALL participants
$stmt = $connection->prepare("
    SELECT u.user_id, u.username, u.email, a.roleOfEvent as role_in_event
    FROM Attendance a
    JOIN User u ON a.user_id = u.user_id
    WHERE a.event_id = ?
    ORDER BY a.isCreator DESC
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$eventEnded = strtotime($event['eventEndTime']) < time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details - JayWing Academy</title>
    
    <!-- bootstrap css -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- custom css -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../components/navbar.php'; ?>

    <main class="container mt-4">
        <div class="card bg-dark text-white">
            <div class="card-header">
                <h2><?php echo htmlspecialchars($event['eventName']); ?></h2>
            </div>
            <div class="card-body">
                <!-- Event Details -->
                <div class="mb-4">
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    <p><strong>Time:</strong> <?php echo date('F j, Y g:i A', strtotime($event['eventStartTime'])); ?> - 
                        <?php echo date('F j, Y g:i A', strtotime($event['eventEndTime'])); ?></p>
                    <p><strong>Type:</strong> <?php echo htmlspecialchars($event['type_name']); ?></p>
                    <p><strong>Wings:</strong> <?php echo htmlspecialchars($event['wings']); ?></p>
                </div>

                <!-- Participants list (visible to creator and participants) -->
                <?php if ($isCreator || $isParticipant): ?>
                    <div class="table-responsive">
                        <h4 class="mb-3">Participants</h4>
                        <?php if ($isCreator): ?>
                            <form id="attendanceForm">
                                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                        <?php endif; ?>
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <?php if ($isCreator): ?>
                                        <th>Attended</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($participants as $participant): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($participant['username']); ?></td>
                                        <td><?php echo htmlspecialchars($participant['role_in_event']); ?></td>
                                        <?php if ($isCreator): ?>
                                            <td>
                                                <input type="checkbox" 
                                                    name="attendance[<?php echo $participant['user_id']; ?>]" 
                                                    value="1">
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if ($isCreator): ?>
                            <button type="submit" class="btn btn-primary">Submit Attendance</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer id="footer"></footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script src="../js/global.js"></script>

    <script>
        document.getElementById('attendanceForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                const response = await fetch('/jaywing-academy/src/data_src/api/events/update_attendance.php', {
                    method: 'POST',
                    body: new FormData(e.target)
                });
                const data = await response.json();
                if (data.success) {
                    alert('Attendance updated successfully');
                    location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to update attendance');
            }
        });
    </script>
</body>
</html>