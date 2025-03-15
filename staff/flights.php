<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: staff_login.php");
    exit();
}
require '../config.php';

// Update flight status if submitted
if (isset($_POST['update_flight'])) {
    $flight_id = $_POST['flight_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE Flights SET status = ? WHERE flight_id = ?");
    $stmt->execute([$new_status, $flight_id]);
    header("Location: flights.php");
    exit();
}

// Pagination & search setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM Flights";
$params = [];
if ($search !== '') {
    $query .= " WHERE flight_number ILIKE ? OR origin ILIKE ? OR destination ILIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$countQuery = $query;
$query .= " ORDER BY flight_id LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Flights Management - Staff</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <?php include 'staff_menu.php'; ?>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold text-red-600 mb-4">Manage Flights</h2>
    <form method="GET" class="mb-4">
      <input type="text" name="search" placeholder="Search flights..." value="<?= htmlspecialchars($search) ?>" class="p-2 border border-gray-300 rounded">
      <button type="submit" class="p-2 bg-red-600 text-white rounded">Search</button>
    </form>
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-200">
          <th class="py-2 px-4 border">ID</th>
          <th class="py-2 px-4 border">Flight Number</th>
          <th class="py-2 px-4 border">Departure</th>
          <th class="py-2 px-4 border">Arrival</th>
          <th class="py-2 px-4 border">Origin</th>
          <th class="py-2 px-4 border">Destination</th>
          <th class="py-2 px-4 border">Status</th>
          <th class="py-2 px-4 border">Update Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($flights as $flight): ?>
        <tr class="text-center">
          <td class="py-2 px-4 border"><?= $flight['flight_id'] ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($flight['flight_number']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($flight['departure']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($flight['arrival']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($flight['origin']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($flight['destination']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($flight['status']) ?></td>
          <td class="py-2 px-4 border">
            <form method="POST">
              <input type="hidden" name="flight_id" value="<?= $flight['flight_id'] ?>">
              <select name="status" class="p-1 border border-gray-300 rounded">
                <option value="scheduled" <?= $flight['status'] == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                <option value="cancelled" <?= $flight['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="completed" <?= $flight['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
              </select>
              <button type="submit" name="update_flight" class="p-1 bg-red-600 text-white rounded">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="mt-4">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="px-3 py-1 border <?= $i == $page ? 'bg-red-600 text-white' : 'bg-white text-red-600' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
</body>
</html>
