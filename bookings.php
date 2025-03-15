<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'config.php';

// Update booking status if submitted
if (isset($_POST['update_booking'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE Bookings SET status = ? WHERE booking_id = ?");
    $stmt->execute([$new_status, $booking_id]);
    header("Location: bookings.php");
    exit();
}

// Process new booking addition
if (isset($_POST['add_booking'])) {
    $user_id = $_POST['user_id'];
    $flight_id = $_POST['flight_id'];
    $seat_number = $_POST['seat_number'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("INSERT INTO Bookings (user_id, flight_id, seat_number, status) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $flight_id, $seat_number, $status]);
    header("Location: bookings.php");
    exit();
}

// Pagination & search setup
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Join Bookings with Users and Flights to show username and flight number
$query = "SELECT b.booking_id, u.username, f.flight_number, b.seat_number, b.status, b.booking_date 
          FROM Bookings b 
          JOIN Users u ON b.user_id = u.user_id 
          JOIN Flights f ON b.flight_id = f.flight_id";
$params = [];
if ($search !== '') {
    $query .= " WHERE u.username ILIKE ? OR f.flight_number ILIKE ? OR b.seat_number ILIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$countQuery = $query;
$query .= " ORDER BY b.booking_id LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total_records = $countStmt->rowCount();
$total_pages = ceil($total_records / $limit);

// For "Add Booking" dropdowns, fetch Users and Flights
$usersStmt = $pdo->query("SELECT user_id, username FROM Users");
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
$flightsStmt = $pdo->query("SELECT flight_id, flight_number FROM Flights");
$flights = $flightsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bookings Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <?php include 'topmenu.php'; ?>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold text-red-600 mb-4">Manage Bookings</h2>
    <!-- Search Form -->
    <form method="GET" class="mb-4">
      <input type="text" name="search" placeholder="Search bookings..." value="<?= htmlspecialchars($search) ?>" class="p-2 border border-gray-300 rounded" />
      <button type="submit" class="p-2 bg-red-600 text-white rounded">Search</button>
    </form>
    
    <!-- Add Booking Form -->
    <div class="mb-6 p-4 bg-white rounded shadow">
      <h3 class="text-xl font-bold mb-2">Add Booking</h3>
      <form method="POST" class="space-y-4">
        <div>
          <label class="block text-gray-700">User</label>
          <select name="user_id" required class="p-2 border border-gray-300 rounded">
            <option value="">Select User</option>
            <?php foreach($users as $user): ?>
              <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-gray-700">Flight</label>
          <select name="flight_id" required class="p-2 border border-gray-300 rounded">
            <option value="">Select Flight</option>
            <?php foreach($flights as $flight): ?>
              <option value="<?= $flight['flight_id'] ?>"><?= htmlspecialchars($flight['flight_number']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-gray-700">Seat Number</label>
          <input type="text" name="seat_number" required class="p-2 border border-gray-300 rounded" />
        </div>
        <div>
          <label class="block text-gray-700">Status</label>
          <select name="status" required class="p-2 border border-gray-300 rounded">
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
            <option value="checked-in">Checked-in</option>
          </select>
        </div>
        <button type="submit" name="add_booking" class="p-2 bg-green-600 text-white rounded">Add Booking</button>
      </form>
    </div>
    
    <!-- Bookings Table -->
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-200">
          <th class="py-2 px-4 border">Booking ID</th>
          <th class="py-2 px-4 border">Username</th>
          <th class="py-2 px-4 border">Flight Number</th>
          <th class="py-2 px-4 border">Seat Number</th>
          <th class="py-2 px-4 border">Status</th>
          <th class="py-2 px-4 border">Booking Date</th>
          <th class="py-2 px-4 border">Update Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($bookings as $booking): ?>
        <tr class="text-center">
          <td class="py-2 px-4 border"><?= $booking['booking_id'] ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($booking['username']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($booking['flight_number']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($booking['seat_number']) ?></td>
          <td class="py-2 px-4 border"><?= $booking['status'] ?></td>
          <td class="py-2 px-4 border"><?= $booking['booking_date'] ?></td>
          <td class="py-2 px-4 border">
            <form method="POST">
              <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
              <select name="status" class="p-1 border border-gray-300 rounded">
                <option value="confirmed" <?= $booking['status']=='confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="cancelled" <?= $booking['status']=='cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="checked-in" <?= $booking['status']=='checked-in' ? 'selected' : '' ?>>Checked-in</option>
              </select>
              <button type="submit" name="update_booking" class="p-1 bg-red-600 text-white rounded">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <!-- Pagination Links -->
    <div class="mt-4">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border <?= $i == $page ? 'bg-red-600 text-white' : 'bg-white text-red-600' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
  <?php include 'footer.php'; ?>
</body>
</html>
