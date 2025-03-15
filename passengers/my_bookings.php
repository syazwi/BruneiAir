<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'passenger') {
    header("Location: passengers_login.php");
    exit();
}
require '../config.php';

$passenger_id = $_SESSION['user_id'];

// Process booking cancellation
if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $stmt = $pdo->prepare("DELETE FROM Bookings WHERE booking_id = ? AND user_id = ?");
    if ($stmt->execute([$booking_id, $passenger_id])) {
        $message = "Booking cancelled successfully!";
    } else {
        $error = "Failed to cancel booking. Please try again.";
    }
}

// Process booking submission
if (isset($_POST['book_flight'])) {
    $flight_id = $_POST['flight_id'];
    $seat_number = $_POST['seat_number'];
    $stmt = $pdo->prepare("INSERT INTO Bookings (user_id, flight_id, seat_number, status) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$passenger_id, $flight_id, $seat_number, 'confirmed'])) {
        $message = "Flight booked successfully!";
    } else {
        $error = "Failed to book the flight. Please try again.";
    }
}

// Fetch available flights (only scheduled flights)
$flightsStmt = $pdo->prepare("SELECT flight_id, flight_number, departure, arrival FROM Flights WHERE status = 'scheduled'");
$flightsStmt->execute();
$availableFlights = $flightsStmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination & search for bookings
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

$query = "SELECT b.booking_id, f.flight_number, f.departure, f.arrival, b.seat_number, b.status, b.booking_date 
          FROM Bookings b 
          JOIN Flights f ON b.flight_id = f.flight_id 
          WHERE b.user_id = ?";
$params = [$passenger_id];
if ($search !== '') {
    $query .= " AND (f.flight_number ILIKE ? OR b.seat_number ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$countQuery = $query;
$query .= " ORDER BY b.booking_date DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total_records = $countStmt->rowCount();
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings - Brunei Airlines</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: transparent;      /* remove color */
  backdrop-filter: blur(2px);   /* apply blur */
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.popup-content {
  background: white;
  border: 4px solid #D71A28;
  padding: 2rem;      /* increased padding */
  border-radius: 0.5rem;
  text-align: center;
  width: 350px;       /* increased width */
  height: 150px;      /* increased height */
}

.popup-content p {
  color: #D71A28;    /* red text */
}

.popup-content button {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background: #D71A28;
  color: white;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
}


    /* When a seat is selected (radio input with class "peer" is checked),
   set the color of the span inside the adjacent div to white */
input[type="radio"].peer:checked + div span {
  color: white !important;
}

  </style>
</head>
<body class="bg-gray-100">
  <?php include 'passengers_topmenu.php'; ?>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold text-red-600 mb-4"><span class="font-bold"><?= htmlspecialchars($username) ?></span>'s Bookings</h2>
    
    <!-- Popup Modal -->
    <?php if (!empty($message) || !empty($error)): ?>
      <div id="popupOverlay" class="popup-overlay flex">
        <div class="popup-content">
          <?php if (!empty($message)): ?>
            <p class="text-green-600 font-bold"><?= $message ?></p>
          <?php endif; ?>
          <?php if (!empty($error)): ?>
            <p class="text-red-600 font-bold"><?= $error ?></p>
          <?php endif; ?>
          <button id="closePopup">Close</button>
        </div>
      </div>
    <?php endif; ?>

    <!-- Booking Form -->
    <div class="mb-6 p-4 bg-white rounded shadow">
      <h3 class="text-xl font-bold mb-4 text-center">Book a Flight</h3>
      <form method="POST" class="space-y-4">
        <!-- Select Flight (vertical top center) -->
        <div class="flex justify-center mb-4">
          <div class="w-full max-w-xs">
            <label class="block text-gray-700 mb-2">Select Flight</label>
            <select name="flight_id" required class="w-full p-2 border border-gray-300 rounded">
              <option value="">Choose Flight</option>
              <?php foreach ($availableFlights as $flight): ?>
                <option value="<?= $flight['flight_id'] ?>">
                  <?= htmlspecialchars($flight['flight_number']) ?> | <?= htmlspecialchars($flight['departure']) ?> to <?= htmlspecialchars($flight['arrival']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <!-- Seat Selection Grid -->
        <div class="flex justify-center">
          <div class="w-full md:w-2/3">
            <label class="block text-gray-700 mb-2 text-center">Select Seat</label>
            <div class="grid grid-cols-11 gap-2">
              <?php 
              $letters = ['A','B','C','D','E','F','G','H','I','J','K'];
              $rows = 7;
              for ($i = 1; $i <= $rows; $i++):
                if ($i == 4):
                  // Row 4 is the aisle, output 11 empty cells.
                  for ($j = 0; $j < 11; $j++):
                    echo '<div class="p-2"></div>';
                  endfor;
                else:
                  for ($j = 0; $j < count($letters); $j++):
                    $seatLabel = $i . $letters[$j];
              ?>
                    <label class="cursor-pointer">
                      <input type="radio" name="seat_number" value="<?= $seatLabel ?>" class="peer hidden" required>
                      <div class="border border-gray-300 p-2 text-center rounded peer-checked:bg-red-600 peer-checked:text-white">
                        <?= $seatLabel ?>
                        <?php if ($j < 3): ?>
                          <br><span class="text-xs text-gray-500">first class</span>
                        <?php else: ?>
                          <br><span class="text-xs text-gray-500">economy</span>
                        <?php endif; ?>
                      </div>
                    </label>
              <?php 
                  endfor;
                endif;
              endfor;
              ?>
            </div>
          </div>
        </div>
        <button type="submit" name="book_flight" class="w-full bg-red-600 text-white py-2 rounded-lg mt-4">
          Book Flight
        </button>
      </form>
    </div>
    
    <!-- Search Bookings -->
    <form method="GET" class="mb-4">
      <div class="flex justify-center">
        <div class="w-full max-w-xs">
          <input type="text" name="search" placeholder="Search bookings..." value="<?= htmlspecialchars($search) ?>" class="p-2 border border-gray-300 rounded w-full">
        </div>
      </div>
      <div class="flex justify-center mt-2">
        <button type="submit" class="p-2 bg-red-600 text-white rounded">Search</button>
      </div>
    </form>
    
    <!-- Bookings Table with Cancel Option -->
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-200">
          <th class="py-2 px-4 border">Booking ID</th>
          <th class="py-2 px-4 border">Flight Number</th>
          <th class="py-2 px-4 border">Departure</th>
          <th class="py-2 px-4 border">Arrival</th>
          <th class="py-2 px-4 border">Seat Number</th>
          <th class="py-2 px-4 border">Status</th>
          <th class="py-2 px-4 border">Booking Date</th>
          <th class="py-2 px-4 border">Cancel</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $booking): ?>
          <tr class="text-center">
            <td class="py-2 px-4 border"><?= $booking['booking_id'] ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($booking['flight_number']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($booking['departure']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($booking['arrival']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($booking['seat_number']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($booking['status']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($booking['booking_date']) ?></td>
            <td class="py-2 px-4 border">
              <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                <button type="submit" name="cancel_booking" class="bg-red-600 text-white px-2 py-1 rounded">Cancel</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt-4 flex justify-center">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border <?= $i == $page ? 'bg-red-600 text-white' : 'bg-white text-red-600' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
  
  <script>
    // Popup handling
    const popupOverlay = document.getElementById('popupOverlay');
    const closePopup = document.getElementById('closePopup');
    if(popupOverlay) {
      popupOverlay.style.display = 'flex';
      closePopup.addEventListener('click', function() {
        popupOverlay.style.display = 'none';
      });
    }
  </script>





  <?php include '../footer.php'; ?>
</body>
</html>
