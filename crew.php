<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require 'config.php';

// Pagination & search setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $limit;

// Join Crew_Assignments with Flights to get flight_number
$query = "SELECT c.assignment_id, c.crew_name, c.role, f.flight_number 
          FROM Crew_Assignments c
          JOIN Flights f ON c.flight_id = f.flight_id";
$params = [];
if ($search !== '') {
    $query .= " WHERE c.crew_name ILIKE ? OR f.flight_number ILIKE ? OR c.role ILIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$countQuery = $query;
$query .= " ORDER BY c.assignment_id LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$crewList = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Crew Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <?php include 'topmenu.php'; ?>
  <div class="container mx-auto p-6">
    <h2 class="text-2xl font-bold text-red-600 mb-4">Manage Crew</h2>
    <!-- Search Form -->
    <form method="GET" class="mb-4">
      <input type="text" name="search" placeholder="Search crew..." value="<?= htmlspecialchars($search) ?>" class="p-2 border border-gray-300 rounded" />
      <button type="submit" class="p-2 bg-red-600 text-white rounded">Search</button>
    </form>
    <!-- Crew Table -->
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="bg-gray-200">
          <th class="py-2 px-4 border">Assignment ID</th>
          <th class="py-2 px-4 border">Flight Number</th>
          <th class="py-2 px-4 border">Crew Name</th>
          <th class="py-2 px-4 border">Role</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($crewList as $crew): ?>
        <tr class="text-center">
          <td class="py-2 px-4 border"><?= $crew['assignment_id'] ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($crew['flight_number']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($crew['crew_name']) ?></td>
          <td class="py-2 px-4 border"><?= htmlspecialchars($crew['role']) ?></td>
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
  <br><br><br><br>
  <?php include 'footer.php'; ?>
</body>
</html>
