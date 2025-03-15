<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'passenger') {
    header("Location: passengers_login.php");
    exit();
}
require '../config.php';

$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM Flights";
$params = [];
if ($search !== '') {
    $query .= " WHERE flight_number ILIKE ? OR origin ILIKE ? OR destination ILIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$countQuery = $query;
$query .= " ORDER BY departure ASC LIMIT $limit OFFSET $offset";

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
  <title>View Flights - Brunei Airlines</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <?php include 'passengers_topmenu.php'; ?>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold text-red-600 mb-4">View Flights</h2>
    <form method="GET" class="mb-4">
      <input type="text" name="search" placeholder="Search flights..." value="<?= htmlspecialchars($search) ?>" class="p-2 border border-gray-300 rounded">
      <button type="submit" class="p-2 bg-red-600 text-white rounded">Search</button>
    </form>
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-200">
          <th class="py-2 px-4 border">Flight ID</th>
          <th class="py-2 px-4 border">Flight Number</th>
          <th class="py-2 px-4 border">Departure</th>
          <th class="py-2 px-4 border">Arrival</th>
          <th class="py-2 px-4 border">Origin</th>
          <th class="py-2 px-4 border">Destination</th>
          <th class="py-2 px-4 border">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($flights as $flight): ?>
          <tr class="text-center">
            <td class="py-2 px-4 border"><?= $flight['flight_id'] ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($flight['flight_number']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($flight['departure']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($flight['arrival']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($flight['origin']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($flight['destination']) ?></td>
            <td class="py-2 px-4 border"><?= htmlspecialchars($flight['status']) ?></td>
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

  <?php include '../footer.php'; ?>
</body>
</html>
