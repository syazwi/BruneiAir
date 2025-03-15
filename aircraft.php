<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'config.php';

// Update aircraft status if submitted
if (isset($_POST['update_aircraft'])) {
    $aircraft_id = $_POST['aircraft_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE Aircraft SET status = ? WHERE aircraft_id = ?");
    $stmt->execute([$new_status, $aircraft_id]);
    header("Location: aircraft.php");
    exit();
}

// Pagination & search setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM Aircraft";
$params = [];
if ($search !== '') {
    $query .= " WHERE model ILIKE ? OR status ILIKE ?";
    $params = ["%$search%", "%$search%"];
}
$countQuery = $query;
$query .= " ORDER BY aircraft_id LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$aircraftList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total count for pagination
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$total_records = $countStmt->rowCount();
$total_pages = ceil($total_records / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Aircraft Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <?php include 'topmenu.php'; ?>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold text-red-600 mb-4">Manage Aircraft</h2>
    <!-- Search Form -->
    <form method="GET" class="mb-4">
      <input type="text" name="search" placeholder="Search aircraft..." value="<?= htmlspecialchars($search) ?>" class="p-2 border border-gray-300 rounded" />
      <button type="submit" class="p-2 bg-red-600 text-white rounded">Search</button>
    </form>
    <!-- Aircraft Table -->
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-200">
          <th class="py-2 px-4 border">Aircraft ID</th>
          <th class="py-2 px-4 border">Model</th>
          <th class="py-2 px-4 border">Capacity</th>
          <th class="py-2 px-4 border">Status</th>
          <th class="py-2 px-4 border">Update Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($aircraftList as $aircraft): ?>
        <tr class="text-center">
          <td class="py-2 px-4 border"><?= $aircraft['aircraft_id'] ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($aircraft['model']) ?></td>
          <td class="py-2 px-4 border"><?= $aircraft['capacity'] ?></td>
          <td class="py-2 px-4 border"><?= $aircraft['status'] ?></td>
          <td class="py-2 px-4 border">
            <form method="POST">
              <input type="hidden" name="aircraft_id" value="<?= $aircraft['aircraft_id'] ?>">
              <select name="status" class="p-1 border border-gray-300 rounded">
                <option value="available" <?= $aircraft['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                <option value="maintenance" <?= $aircraft['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                <option value="in use" <?= $aircraft['status'] == 'in use' ? 'selected' : '' ?>>In Use</option>
              </select>
              <button type="submit" name="update_aircraft" class="p-1 bg-red-600 text-white rounded">Update</button>
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
  <br>
  <?php include 'footer.php'; ?>
</body>
</html>
